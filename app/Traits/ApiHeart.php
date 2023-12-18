<?php

namespace App\Traits;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

trait ApiHeart
{
    public function initHttpWithToken(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders($this->apiHeaders())->withToken($this->getBeeToken());
    }

    public function apiHeaders(): array
    {
        return ['Accept' => 'application/json', 'Content-Type' => 'application/json', 'api-version' => '1'];
    }

    public function getBeeToken(): string
    {
        return $this->checkLogin() ? session('login')['data']['token'] : '';
    }

    public function initDoptorHttp(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders($this->apiHeaders())->withToken($this->getDoptorToken($this->getUsername()));
    }



    //this method return token for session or by generating token from getClientToken()

    public function getDoptorToken($username)
    {
        $url = config('n_doptor_api.auth.client_login_url');
        $client_id = config('n_doptor_api.auth.client_id');
        $client_pass = config('n_doptor_api.auth.client_pass');
 

        if (!session()->has('_doptor_token') || session('_doptor_token') == '') {
            $token = $this->getClientToken($url, $client_id, $client_pass, $username);
            session(['_doptor_token' => $token]);
        }
        
        return session('_doptor_token');
    }


    //it will generate token if not available in sesion
    public function getClientToken($url, $client_id, $client_pass, $username = '')
    {
        if ($username == '') {
            $getToken = $this->initHttp()->post($url, ['client_id' => $client_id, 'password' => $client_pass]);
        } else {
            $getToken = $this->initHttp()->post($url, ['client_id' => $client_id, 'password' => $client_pass, 'username' => $username,]);
        }

        if ($getToken->status() == 200 && $getToken->json()['status'] == 'success') {

            //dd($getToken->json()['data']['token']);
            return $getToken->json()['data']['token'];
        } else {
            return '';
        }
    }

    public function initHttp(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders($this->apiHeaders());
    }
}
