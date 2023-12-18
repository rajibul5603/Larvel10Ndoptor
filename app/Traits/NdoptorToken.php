<?php
namespace App\Traits;

use Illuminate\Support\Facades\Http;
 
trait NdoptorToken
{ 
    public static $api_domain = 'https://n-doptor-api.nothi.gov.bd/'; // Live Nothi Url

    public static $current_username= ""; 

 
    public static function getToken() {
        $url = config('n_doptor_api.auth.client_login_url');
        $client_id = config('n_doptor_api.auth.client_id');
        $client_pass = config('n_doptor_api.auth.client_pass');
 
        //dd($url);
        $userName = session('login')['user_info']['user']['username']??"";

        self::$current_username= $userName;

        //dd($userName);

        if(session('ndoptor_token')){
            return session('ndoptor_token');
        }
        else{

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'api-version' => '1', 
            ])->post($url, [
                'client_id' => $client_id, 
                'password' => $client_pass, 
                'username' => self::$current_username, 
            ]);

            if ($response->json()['status'] == 'error') {
                
                return redirect('/login')->withErrors('Nothi Connection Problem');
            } else {
                $data = $response->json()['data'];
                session()->put('ndoptor_token', $data['token']); 
                session()->save();
                return $data['token']; 

            }
        }
    }

}

?>