<?php

namespace Cncw;

class Uri {
    public static function httpQuery2($orderBy=NULL, $sort='ASC') {
        $data = array(
            'mod' => $_GET['mod'],
            'id' => $_GET['id'],
            'member_id' => NULL,
            'member_name' => NULL,
            'member_phone' => NULL,
            'transaction_date' => NULL,
            'created_at' => NULL,
            'orderBy' => $orderBy,
            'sort' => $sort
        );
        $_mid = new \Valitron\Validator($_GET);
        $_mid->rule('required', 'member_id');
        if($_mid->validate()) {
            $data['member_id'] = $_GET['member_id'];
        }
        $_mnm = new \Valitron\Validator($_GET);
        $_mnm->rule('required', 'member_name');
        if($_mnm->validate()) {
            $data['member_name'] = $_GET['member_name'];
        }
        $_mph = new \Valitron\Validator($_GET);
        $_mph->rule('required', 'member_phone');
        if($_mph->validate()) {
            $data['member_phone'] = $_GET['member_phone'];
        }
        $_tsd = new \Valitron\Validator($_GET);
        $_tsd->rule('required', 'transaction_date');
        if($_tsd->validate()) {
            $data['transaction_date'] = $_GET['transaction_date'];
        }
        $_cat = new \Valitron\Validator($_GET);
        $_cat->rule('required', 'created_at');
        if($_cat->validate()) {
            $data['created_at'] = $_GET['created_at'];
        }

        $vget = new \Valitron\Validator($_GET);
        $vget->rule('required', 'orderBy');
        $vget->rule('required', 'sort');
        if($vget->validate()) {
            if ($orderBy == $_GET['orderBy']) {
                if ($_GET['sort'] == 'ASC') {
                    $data['sort'] = 'DESC';
                } elseif ($_GET['sort'] == 'DESC') {
                    $data['sort'] = 'ASC';
                }
            }
        }

        return http_build_query($data);
    }

    public static function sendLink() {
        $_orderBy = NULL;
        $vkir = new \Valitron\Validator($_GET);
        $vkir->rule('required', 'orderBy');
        if($vkir->validate()) {
            $_orderBy = $_GET['orderBy'];
        }
        return $_orderBy;
    }

    public static function pageLink() {
        $_page = NULL;
        $vpag = new \Valitron\Validator($_GET);
        $vpag->rule('required', 'page');
        if($vpag->validate()) {
            $_page = $_GET['page'];
        }
        return $_page;
    }


}


