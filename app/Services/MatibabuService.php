<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class MatibabuService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.matibabu.base_url');
    }

    public function getToken()
    {
        return Cache::remember('matibabu_token', now()->addMinutes(50), function () {

            $response = Http::post(
                $this->baseUrl . '/api/login',
                [
                    'userName' => config('services.matibabu.username'),
                    'password' => config('services.matibabu.password')
                ]
            );

            if (!$response->successful()) {
                throw new \Exception('Matibabu authentication failed');
            }

            return $response->json()['access_token'];
        });
    }

    public function enquireInsuree(string $matibabuCard)
    {
        $token = $this->getToken();

        $response = Http::withToken($token)
            ->acceptJson()
            ->get(
                $this->baseUrl . '/api/insuree/' . $matibabuCard . '/enquire'
            );

        if (!$response->successful()) {
            throw new \Exception(
                'Failed to enquire insuree: ' . $response->body()
            );
        }

        return $response->json();
    }
}