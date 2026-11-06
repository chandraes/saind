<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class WaStatus
{
    private $apikey;

    public function __construct()
    {
        $this->apikey = config('services.starsender.key');
    }

    public function getStatusWa()
    {
        $apikey = $this->apikey;

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://starsender.online/api/v1/getDevice',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => [
                'apikey: '.$apikey,
            ],
        ]);

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
        $apikey = $this->apikey;

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://starsender.online/api/relogDevice',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => [
                'apikey: '.$apikey,
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        $result = json_decode($response, true);
        dd($result);

    }

    /** @return array{success: true, data: list<array{id: string, name: string}>}|false */
    public function getGroup(): array|false
    {
        if (! is_string($this->apikey) || trim($this->apikey) === '') {
            return false;
        }
        try {
            $response = Http::acceptJson()->connectTimeout(3)->timeout(10)->withoutRedirecting()
                ->withHeaders(['Authorization' => $this->apikey])
                ->get('https://api.starsender.online/api/whatsapp/groups');
        } catch (ConnectionException) {
            return false;
        }
        $result = $response->json();
        if (! $response->successful() || ! is_array($result) || ($result['success'] ?? false) !== true
            || ! is_array($result['data']['groups'] ?? null)) {
            return false;
        }
        $groups = [];
        foreach ($result['data']['groups'] as $group) {
            if (! is_array($group) || ! is_string($group['id'] ?? null) || trim($group['id']) === ''
                || ! is_string($group['name'] ?? null) || trim($group['name']) === '') {
                continue;
            }
            $groups[] = ['id' => $group['id'], 'name' => $group['name']];
        }

        return ['success' => true, 'data' => $groups];
    }
}
