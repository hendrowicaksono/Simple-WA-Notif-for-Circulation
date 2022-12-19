<?php
/**
 * Plugin Name: Simple Circulation Notification using Whatsapp
 * Plugin URI:
 * Description: Using API provided by WHACENTER (https://whacenter.com/). 
 * Version: 1.0.0
 * Author: Hendro Wicaksono
 * Author URI: https://github.com/hendrowicaksono
 */

defined('INDEX_AUTH') OR die('Direct access not allowed!');

use SLiMS\DB;
use SLiMS\Plugins;

// IP based access limitation
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-circulation');

// privileges checking
$can_read = utility::havePrivilege('circulation', 'r');

require 'vendor/autoload.php';
require 'bootstrap.php';

$ccnw = array ();
$ccnw['conn'] = $conn;
$ccnw['library_name'] = $library_name;
$ccnw['device_id'] = $device_id;
$ccnw['footer_text'] = $footer_text;

// get plugin instance
$plugin = \SLiMS\Plugins::getInstance();

$plugin->registerMenu('circulation', __('WA Notif Log'), __DIR__ . '/index.php');

// registering menus or hook
$plugin->register("circulation_after_successful_transaction", function($data) use (&$ccnw) {
    # Make sure that data sent to whacenter contains transaction(s).
    if ( (isset($data['loan'])) OR (isset($data['return'])) OR (isset($data['extend'])) ) {
        # Getting member data to get member_phone info.
        $member_data = api::member_load(DB::getInstance('mysqli'), $data['memberID']);
        # Tambahkan validasi member_phone disini jika dibutuhkan. Kalau nomer
        # tidak ada atau tidak valid, tidak usah diproses.
        $member_phone = new Valitron\Validator($member_data[0]);
        $member_phone->rule('required', ['member_phone']);
        if($member_phone->validate()) {

            # HEADER
            $message = '*'.strtoupper($ccnw['library_name'])."*\n";
            $message .= 'No. Angg : '.$data['memberID']."\n";
            $message .= 'Nama : '.$data['memberName']."\n";
            $message .= 'Jn. Angg : '.$data['memberType']."\n";
            $message .= 'Tanggal : '.$data['date']."\n";
            $messageId = substr(sha1(rand(1, 20).date('UTC')), 0, 16);
            $message .= 'ID : '.$messageId."\n";

            # PEMINJAMAN
            if (isset($data['loan'])) {
                $message .= "=====================\n";
                $message .= "*PEMINJAMAN*\n";
                $message .= "=====================\n";
                foreach($data['loan'] as $lk => $lv) {
                    $message .= '*'.$lv['itemCode']."*\n";
                    $message .= '_'.$lv['title']."_\n";
                    $loanDate = explode('-', $lv['loanDate']);
                    $message .= 'Tanggal pinjam: '.$loanDate[2].'-'.$loanDate[1].'-'.$loanDate[0]."\n";
                    $dueDate = explode('-', $lv['dueDate']);
                    $message .= 'Batas pinjam: '.$dueDate[2].'-'.$dueDate[1].'-'.$dueDate[0]."\n";
                }
            }

            # PENGEMBALIAN
            if (isset($data['return'])) {
                $counter = 0;
                $retmessage = "=====================\n";
                $retmessage .= "*PENGEMBALIAN*\n";
                $retmessage .= "=====================\n";
                foreach($data['return'] as $rk => $rv) {
                    $dup = FALSE;
                    if (isset($data['extend'])) {
                        foreach ($data['extend'] as $_ek => $_ev) {
                            if ($rv['itemCode'] == $_ev['itemCode']) {
                                $dup = TRUE;
                            }
                        }
                    }
                    if (!$dup) {
                        $retmessage .= '*'.$rv['itemCode']."*\n";
                        $retmessage .= '_'.$rv['title']."_\n";
                        $returnDate = explode('-', $rv['returnDate']);
                        $retmessage .= 'Tanggal kembali: '.$returnDate[2].'-'.$returnDate[1].'-'.$returnDate[0]."\n";
                        if ($rv['overdues']) {
                            $retmessage .= 'Denda: '.$rv['overdues']."\n";
                        }
                        $counter++;                  
                    }
                }
                if ($counter > 0) {
                    $message .= $retmessage;
                }
            }

            # PERPANJANGAN
            if (isset($data['extend'])) {
                $message .= "=====================\n";
                $message .= "*PERPANJANGAN*\n";
                $message .= "=====================\n";
                foreach($data['extend'] as $ek => $ev) {
                    $message .= '*'.$ev['itemCode']."*\n";
                    $message .= '_'.$ev['title']."_\n";
                    $loanDate = explode('-', $ev['loanDate']);
                    $message .= 'Tanggal pinjam: '.$loanDate[2].'-'.$loanDate[1].'-'.$loanDate[0]."\n";
                    $dueDate = explode('-', $ev['dueDate']);
                    $message .= 'Batas pinjam: '.$dueDate[2].'-'.$dueDate[1].'-'.$dueDate[0]."\n";
                }
            }

            # FOOTER
            $message .= "\n_____________________\n".$ccnw['footer_text'];

            # Simpan log ke database
            #$query = DB::getInstance()->prepare("INSERT INTO circ_notif_wa_log (member_id, member_name, member_type, transaction_date, transaction_id, message, created_at) 
            #VALUES (:member_id, :member_name, :member_type, :transaction_date, :transaction_id, :message, :created_at)");
            $query = $ccnw['conn']->prepare("INSERT INTO circ_notif_wa_log (member_id, member_name, member_type, member_phone, transaction_date, transaction_id, message, created_at) 
            VALUES (:member_id, :member_name, :member_type, :member_phone, :transaction_date, :transaction_id, :message, :created_at)");
            $query->bindValue(':member_id', $data['memberID'], PDO::PARAM_STR);
            $query->bindValue(':member_name', $data['memberName'], PDO::PARAM_STR);
            $query->bindValue(':member_type', $data['memberType'], PDO::PARAM_STR);
            $query->bindValue(':member_phone', $member_data[0]['member_phone'], PDO::PARAM_STR);
            $query->bindValue(':transaction_date', $data['date']);
            $query->bindValue(':transaction_id', $messageId, PDO::PARAM_STR);
            $query->bindValue(':message', $message);
            $query->bindValue(':created_at', date('Y-m-d H:i:s'));
            $query->execute();

            # informasi kredensial whacenter
            $data = array (
                'device_id' => $ccnw['device_id'],
                'number' => $member_data[0]['member_phone'],
                'message' => $message
            );
            \Cncw\Notification::sendToWhacenter($data);
        }
    }
});
