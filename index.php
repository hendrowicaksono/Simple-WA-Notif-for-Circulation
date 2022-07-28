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
use \Cncw\Uri;

// IP based access limitation
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-circulation');
// start the session
require SB . 'admin/default/session.inc.php';
require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO . 'simbio_DB/datagrid/simbio_dbgrid.inc.php';

// privileges checking
$can_read = utility::havePrivilege('circulation', 'r');
if (!$can_read) {
    die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
} 
require 'vendor/autoload.php';
require 'bootstrap.php';

$vkid = new Valitron\Validator($_GET);
$vkid->rule('required', 'kirim_id');
$vkid->rule('integer', 'kirim_id');
if($vkid->validate()) {
    $s_get_log = 'SELECT * FROM circ_notif_wa_log WHERE id='.$_GET['kirim_id'];
    $q_get_log = $conn->query($s_get_log);
    if ($q_get_log->rowCount() > 0) {
        $d_get_log = $q_get_log->fetchAll();

        $ccnw = array ();
        $ccnw['device_id'] = $device_id;     

        $data = array (
            'device_id' => $ccnw['device_id'],
            'number' => $d_get_log[0]['member_phone'],
            'message' => $d_get_log[0]['message']
        );
        \Cncw\Notification::sendToWhacenter($data);
?>
        <div class="alert alert-primary alert-dismissible fade show" role="alert">
            <strong>Data terkirim!</strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
<?php
    }
}
?>
<div class="menuBox">
    <div class="menuBoxInner printIcon">
        <div class="per_title">
            <h2><?php echo __('Circulation Notification Log'); ?></h2>
        </div>
        <div class="infoBox">
            <?= __('Enter form below to filtered result!') ?>
        </div>
        <div class="<?= empty($flashError) ? 'd-none' : 'alert alert-warning font-weight-bold' ?>">
            <?= $flashError ?>
        </div>
        <div class="sub_section">
            <form name="read_counter" action="<?= $_SERVER['PHP_SELF'] . '?' . \Cncw\Uri::httpQuery2() ?>" id="search" method="get"
                  class="form-inline"><?php echo __('Member ID'); ?>&nbsp;:&nbsp;
                <input type="text" name="member_id" class="form-control col-md-1" autocomplete="off"/>
                &nbsp;&nbsp;<?php echo __('Member Name'); ?>&nbsp;:&nbsp;
                <input type="text" name="member_name" class="form-control col-md-1" autocomplete="off"/>
                &nbsp;&nbsp;<?php echo __('Member Phone'); ?>&nbsp;:&nbsp;
                <input type="text" name="member_phone" class="form-control col-md-1" autocomplete="off"/>
                &nbsp;&nbsp;<?php echo __('Transaction Date'); ?>&nbsp;:&nbsp;
                <input type="text" name="transaction_date" class="form-control col-md-1" autocomplete="off"/>
                <input type="submit" id="doAdd" value="<?php echo __('Search'); ?>"
                       class="s-btn btn btn-success"/>
            </form>
        </div>
    </div>
</div>

<?php
$log =  new \Cncw\Log();
$res = $log->getData();

if (count($res) > 0) {
?> 
<div class="alert alert-success" role="alert">
  Found log data: <?= $log->getTotal() ?>
</div>
<table class="table table-striped">
    <tbody>
        <tr>
            <td>id</td>
            <td><a href="<?= $_SERVER['PHP_SELF'] . '?' . \Cncw\Uri::httpQuery2('member_id') ?>">member_id</a></td>
            <td><a href="<?= $_SERVER['PHP_SELF'] . '?' . \Cncw\Uri::httpQuery2('member_name') ?>">member_name</a></td>
            <td><a href="<?= $_SERVER['PHP_SELF'] . '?' . \Cncw\Uri::httpQuery2('member_type') ?>">member_type</a></td>
            <td><a href="<?= $_SERVER['PHP_SELF'] . '?' . \Cncw\Uri::httpQuery2('member_phone') ?>">member_phone</a></td>
            <td><a href="<?= $_SERVER['PHP_SELF'] . '?' . \Cncw\Uri::httpQuery2('transaction_date') ?>">transaction_date</a></td>
            <td>transaction_id</td>
            <td><a href="<?= $_SERVER['PHP_SELF'] . '?' . \Cncw\Uri::httpQuery2('created_at') ?>">created_at</a></td>
            <td></td>
        </tr>

<?php
    foreach($res as $kr => $vr) {
?>
        <tr class="alterCell2">
            <td valign="top"><?= $vr['id'] ?></td>
            <td valign="top"><?= $vr['member_id'] ?></td>
            <td valign="top"><?= $vr['member_name'] ?></td>
            <td valign="top"><?= $vr['member_type'] ?></td>
            <td valign="top"><?= $vr['member_phone'] ?></td>
            <td valign="top"><?= $vr['transaction_date'] ?></td>
            <td valign="top"><?= $vr['transaction_id'] ?></td>
            <td valign="top"><?= $vr['created_at'] ?></td>
            <td valign="top">
                <a class="" href="<?= $_SERVER['PHP_SELF'] . '?' . \Cncw\Uri::httpQuery2() ?>&amp;orderBy=<?= \Cncw\Uri::sendLink() ?>&amp;page=<?= \Cncw\Uri::pageLink() ?>&amp;kirim_id=<?= $vr['id'] ?>" title="<?= $vr['message'] ?>"><span>Kirim ulang</span></a>            
            </td>
        </tr>
<?php
    }
?>
    </tbody>
</table>
<?php
} else {
?>
<div class="alert alert-warning" role="alert">
    Log data not found!
</div>
<?php
}
if ($log->getTotal() > 0) {
?>
<div>
<?=\yidas\widgets\Pagination::widget([
    'pagination' => $log->getPagination()
])?>
</div>
<?php 
}
?>