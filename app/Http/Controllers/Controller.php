<?php

namespace App\Http\Controllers;

use App\Traits\UserInfoCollector;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, UserInfoCollector;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $is_logged_in = $this->checkLogin() ? 'true' : 'false';
            view()->share('is_logged_in', $is_logged_in);

            // dd(session('login'));  //"status" => true;  "is_admin" => false  "is_unit_admin" => false;  "is_unit_head" => false

            if ($this->checkLogin()) {
                self::viewSharer();
            }
            return $next($request);
        });

    }

    private function viewSharer()
    {
        $wizard = $this->wizard();
        view()->share('wizardData', $wizard);
        $userDetails = $this->getUserDetails();
        view()->share('userDetails', $userDetails);

        $employeeInfo = $this->getEmployeeInfo();
        view()->share('employeeInfo', $employeeInfo);

        $userOffices = $this->getUserOffices();
        view()->share('userOffices', $userOffices);

        $currentOffice = $this->current_office();
        view()->share('currentOffice', $currentOffice);

    }

    public function wizard()
    {
        if (!session('_wizard')) {
            $http = new \GuzzleHttp\Client(['verify' => false]);
            $response = $http->get(config('n_doptor_api.widget'));
            $data = json_decode($response->getBody()->getContents(), true);
            session()->put(['_wizard' => $data['data']]);
            session()->save();
        }

        return session('_wizard');
    }
 
}
