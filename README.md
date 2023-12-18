**Fist install ndoptor package for laravel 10 **
add these line in composer.json 
==================================
```
 "require": {
        "php": "^8.1",
        "guzzlehttp/guzzle": "^7.2",
        "laravel/framework": "^10.10",
        "laravel/sanctum": "^3.3",
        "laravel/tinker": "^2.8",
        "ndoptor/integration-sso-laravel": "^3.2"
    },
```

Change this file in vendor>ndoptor folder:  NdoptorSSOController.php 
==================================
```
<?php

namespace Ndoptor\SSO\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; 
 

class NdoptorSSOController extends Controller
{
    public function showLoginForm(Request $request)
    {
        $redirect_url = url('/login-response');

        if ($request->has('redirect') && $request->get('redirect') != '') {
            $redirect_url .= '?redirect=' . ($request->get('redirect'));
        }
        return redirect(config('ndoptor.login_sso_url') . '?referer=' . base64_encode(url($redirect_url)));
    }

    public function loginResponse(Request $request)
    {
       $data = json_decode(gzuncompress(base64_decode($request->data)), true);

        if ($data['status'] == 'success' && !empty($data['user_info'])) {
            
            $user_info = $data['user_info'];
            session()->put(['_ndoptor_loggedin_user_session' => $user_info]);
            session()->save();

			$expires = strtotime('+1 days');

			 $this->createNewCookie($data, $expires);

			if (method_exists($this, 'afterLogin')) {
				$this->afterLogin($request);
			}
			if ($request->has('redirect') && $request->get('redirect') != '') {
				return redirect($request->get('redirect'));
			} else {
				return redirect('/');
			}
		} else {
			return redirect('/login');
		}
    }
 
    public function createNewCookie($cookie_data) {
        $cookie_data = base64_encode(gzcompress(json_encode($cookie_data)));
    
		setcookie('_ndoptor', $cookie_data, strtotime( '+1 days' ), '/');
    
		return $cookie_data;
	}

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        unset($_COOKIE['_ndoptor']);
        setcookie('_ndoptor', null, strtotime('-1 days'), '/');
        if (method_exists($this, 'afterLogout')) {
            $this->afterLogout($request);
        }
        return redirect(config('ndoptor.logout_sso_url') . '?referer=' . base64_encode(url('/login-response')));
    }
}
```



src/config/ndoptor.php
=============================
```
<?php

return [
	/*
    |--------------------------------------------------------------------------
    | Login SSO
    |--------------------------------------------------------------------------
    |
    | This value for SSO enable or disable.
    |
    */
	'login_sso' => env('LOGIN_SSO', true),

	/*
    |--------------------------------------------------------------------------
    | Login SSO URL
    |--------------------------------------------------------------------------
    |
    | This value is the url for sso login authentication.
    |
    */
	'login_sso_url' => env('LOGIN_SSO_URL', 'https://api-stage.doptor.gov.bd/login'),

	/*
    |--------------------------------------------------------------------------
    | Logout SSO URL
    |--------------------------------------------------------------------------
    |
    | This value is the url for sso logout authentication.
    |
    */
	'logout_sso_url' => env('LOGOUT_SSO_URL', 'https://api-stage.doptor.gov.bd/logout'),

	/*
    |--------------------------------------------------------------------------
    | API URL
    |--------------------------------------------------------------------------
    |
    | This value is the url for api url.
    |
    */
	'api_url' => env('NDOPTOR_API_URL', 'https://api-stage.doptor.gov.bd/api'),

];
```

**Add this line .env file**
```
# this is very confidential 

#live -- https://n-doptor-accounts.nothi.gov.bd/

#NDOPTOR_API_URL=https://n-doptor-accounts-stage.nothi.gov.bd/api
NDOPTOR_API_URL=https://api-stage.doptor.gov.bd/api
#LOGIN_SSO_URL=https://n-doptor-accounts-stage.nothi.gov.bd/login
LOGIN_SSO_URL=https://api-stage.doptor.gov.bd/login
#LOGOUT_SSO_URL= https://n-doptor-accounts-stage.nothi.gov.bd/logout
LOGOUT_SSO_URL= https://api-stage.doptor.gov.bd/logout
``` 
