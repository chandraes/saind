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

    public function getGroup(): array|false
    {
        if (blank($this->apikey)) {
            return false;
        }
        try {
            $response = Http::acceptJson()->withHeaders(['Authorization' => $this->apikey])
                ->connectTimeout(5)->timeout(15)->get('https://api.starsender.online/api/whatsapp/groups');
        } catch (ConnectionException) {
            return false;
        }
        $result = $response->json();
        if (! $response->successful() || ! is_array($result) || ($result['success'] ?? false) !== true || ! is_array($result['data'] ?? null)) {
            return false;
        }
        $groups = $result['data']['groups'] ?? $result['data'];
        if (! is_array($groups)) {
            return false;
        }
        $groups = array_values(array_filter($groups, fn ($group): bool => is_array($group) && isset($group['id'], $group['name']) && is_scalar($group['id']) && is_string($group['name'])));

        return ['success' => true, 'data' => $groups];
    }
}
