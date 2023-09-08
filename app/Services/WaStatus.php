<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WaStatus
{
    private $apikey;

    function __construct()
    {
        $this->apikey = env('STARSENDER_KEY');
    }

    public function getStatusWa()
    {
        $apikey=$this->apikey;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://starsender.online/api/v1/getDevice',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'apikey: '.$apikey
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $result = json_decode($response, true);

        if ($result['status'] == true) {
            return $result;
        } else {
            return false;
        }

    }

    public function getRelog()
    {
        $apikey=$this->apikey;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://starsender.online/api/relogDevice',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'apikey: '.$apikey
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $result = json_decode($response, true);
        dd($result);

    }

    public function getGroup()
    {
        $apikey=$this->apikey;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://starsender.online/api/device/group',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'apikey: '.$apikey
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $result = json_decode($response, true);

        if ($result['status'] == true) {
            return $result;
        } else {
            return false;
        }
    }
}
