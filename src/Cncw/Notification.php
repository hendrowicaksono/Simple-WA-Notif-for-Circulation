<?php

namespace Cncw;

class Notification {
    public static function sendToWhacenter($data) {
        #return 'Sent to Whacenter API';
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', 'https://app.whacenter.com/api/send', [
            'form_params' => $data
        ]);
    }
}


