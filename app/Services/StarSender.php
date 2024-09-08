<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class StarSender
{
    private $apikey, $tujuan, $pesan, $file;

    function __construct($tujuan, $pesan, $file = null)
    {
        $this->apikey = env('STARSENDER_KEY');
        $this->tujuan = $tujuan;
        $this->pesan = $pesan;
        $this->file = $file;
    }

    public function sendGroup()
    {
        $apikey=$this->apikey;
        // $tujuan=$this->tujuan; //atau $tujuan="Group Chat Name";
        // $pesan=$this->pesan;
        $file=$this->file;

        $pesan = [
            'messageType' => 'text',
            'to' => $this->tujuan,
            'body' => $this->pesan,
            'file' => $file
        ];
        $curl = curl_init();


        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.starsender.online/api/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($pesan),
            CURLOPT_HTTPHEADER => array(
                'Content-Type:application/json',
                'Authorization: '.$apikey
            ),
        ));

        // curl_setopt_array($curl, array(
        //     CURLOPT_URL => 'https://starsender.online/api/group/sendText?message='.rawurlencode($pesan).'&tujuan='.rawurlencode($tujuan),
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => '',
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 0,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_CUSTOMREQUEST => 'POST',
        //     CURLOPT_HTTPHEADER => array(
        //         'apikey: '.$apikey
        //     ),
        // ));

        $response = curl_exec($curl);

        curl_close($curl);

        $result = json_decode($response, true);
        // dd($result);
        if ($result['success'] == true) {
            return true;
        } else {
            return false;
        }

    }

    public function sendWa()
    {
        $apikey=$this->apikey;
        $tujuan=$this->tujuan; //atau $tujuan="Group Chat Name";
        $pesan=$this->pesan;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://starsender.online/api/sendText?message='.rawurlencode($pesan).'&tujuan='.rawurlencode($tujuan.'@s.whatsapp.net'),
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

        if ($result['success'] == true) {
            return true;
        } else {
            return false;
        }

    }

}
