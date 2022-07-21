<?php
/**
 * Plugin Name: Simple Circulation Notification using Whatsapp
 * Plugin URI:
 * Description: Using API provided by WHACENTER (https://whacenter.com/). 
 * Version: 1.0.0
 * Author: Hendro Wicaksono
 * Author URI: https://github.com/hendrowicaksono
 */

use SLiMS\DB;
use SLiMS\Plugins;

require 'vendor/autoload.php';

$ccnw = array ();
$ccnw['library_name'] = 'Perpustakaan Ideal Serbaguna';
$ccnw['device_id'] = 'put_your_device_id_here';
$ccnw['footer_text'] = 'Harap simpan resi ini sebagai bukti transaksi.';

// get plugin instance
$plugin = \SLiMS\Plugins::getInstance();

// registering menus or hook
$plugin->register("circulation_after_successful_transaction", function($data) use (&$ccnw) {
    # Make sure that data sent to whacenter contains transaction(s).
    if ( (isset($data['loan'])) OR (isset($data['return'])) OR (isset($data['extend'])) ) {
        # Getting member data to get member_phone info.
        $member_data = api::member_load(DB::getInstance('mysqli'), $data['memberID']);
        # Tambahkan validasi member_phone disini jika dibutuhkan. Kalau nomer
        #tidak ada atau tidak valid, tidak usah diproses.
        if (isset($member_data[0]['member_phone'])) {

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
        
            # informasi kredensial whacenter
            $data = array (
                'device_id' => $ccnw['device_id'],
                'number' => $member_data[0]['member_phone'],
                'message' => $message
            );
            # kirim request
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', 'https://app.whacenter.com/api/send', [
                'form_params' => $data
            ]);

        }
    }
});