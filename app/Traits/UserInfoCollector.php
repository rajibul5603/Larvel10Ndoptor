<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

trait UserInfoCollector
{
    use ApiHeart;

    public function getUserDetails()
    {
        // dd(config('n_doptor_api.auth.client_id'));
       //  dd($this->getDoptorToken($this->getUsername()));
        // dd($this->getDoptorToken('200000000163'));
        return session()->has('login') ? session('login')['user_info']['user'] : null;
    }

    function getDeskInformation($cdesk)
    {
        return [
            'office_id' => $cdesk['office_id'],
            'office_unit_id' => $cdesk['office_unit_id'],
            'designation_id' => $cdesk['office_unit_organogram_id'],
            'officer_id' => $cdesk['employee_record_id'],
            'user_primary_id' => $cdesk['user_primary_id'],
            'user_id' => $cdesk['user_id'],
            'office' => $cdesk['office_name_bn'],
            'office_unit' => $cdesk['unit_name_en'],
            'designation' => $cdesk['designation'],
            'officer' => $cdesk['officer_name'],
            'officer_grade' => $cdesk['employee_grade'],
            'designation_level' => $cdesk['designation_level'],
            'designation_sequence' => $cdesk['designation_sequence'],
            'email' => $cdesk['email'],
            'phone' => $cdesk['phone'],
        ];
    }

    function current_desk(): array
    {
        return [
            'office_id' => $this->current_office_id(),
            'office_unit_id' => $this->current_office_unit_id(),
            'designation_id' => $this->current_designation_id(),
            'officer_id' => $this->getOfficerId(),
            'user_primary_id' => $this->getUserId(),
            'user_id' => $this->getUsername(),
            'office' => $this->current_office()['office_name_en'],
            'office_unit_en' => $this->current_office()['unit_name_en'],
            'office_unit_bn' => $this->current_office()['unit_name_bn'],
            'designation_en' => $this->current_office()['designation_en'],
            'designation_bn' => $this->current_office()['designation'],
            'officer_en' => $this->getEmployeeInfo()['name_eng'],
            'officer_bn' => $this->getEmployeeInfo()['name_bng'],
            'designation_level' => $this->current_office()['designation_level'],
            'designation_sequence' => $this->current_office()['designation_sequence'],
            'officer_grade' => $this->getEmployeeInfo()['employee_grade'],
            'email' => $this->getEmployeeInfo()['personal_email'],
            'phone' => $this->getEmployeeInfo()['personal_mobile'],
        ];
    }

    public function current_office_id()
    {
        return session('_office_id') ?: $this->getUserOffices()[0]['office_id'];
    }

    public function getUserOffices()
    {
        return $this->checkLogin() ? session('login')['user_info']['office_info'] : [];
    }

    public function checkLogin(): bool
    {
        $login_session = session('login') ?: $this->setLogin();
        return (bool)$login_session;
    }

    public function setLogin()
    {

        $login_cookie = isset($_COOKIE['_ndoptor']) ? $_COOKIE['_ndoptor'] : null;
        if ($login_cookie) {
            $login_data_from_cookie = json_decode(gzuncompress(base64_decode($login_cookie)), true);
            if ($login_data_from_cookie && $login_data_from_cookie['status'] === 'success') {
                //setcookie('_ndoptor', $login_data_from_cookie, strtotime('+1 days'), '/');

                session()->put('login', $login_data_from_cookie);
                session()->save();
                return session('login');
            }
        }
        return null;
    }

    public function current_office_unit_id()
    {
        return session('_office_unit_id') ?: $this->getUserOffices()[0]['office_unit_id'];
    }

    public function current_designation_id()
    {
        return session('_designation_id') ?: $this->getUserOffices()[0]['office_unit_organogram_id'];
    }
    public function current_designation_name()
    {
        return session('_designation_name') ?: $this->getUserOffices()[0]['designation'];
    }


    public function getOfficerId()
    {
        return $this->checkLogin() ? session('login')['user_info']['user']['employee_record_id'] : null;
    }

    public function getUserId()
    {
        return $this->checkLogin() ? session('login')['user_info']['user']['id'] : null;
    }

    public function getUsername()
    {
        return $this->checkLogin() ? session('login')['user_info']['user']['username'] : null;
    }

    public function current_office()
    {
        return session('_current_office') ?: $this->getUserOffices()[0];
    }

    public function getEmployeeInfo()
    {
        return session()->has('login') ? session('login')['user_info']['employee_info'] : null;
    }

    public function loginIntoCagBee($data)
    {
        return session('login') ?: $this->loginIntoCagBeeCore($data);
    }

    public function current_office_domain()
    {
        return $this->current_office()['office_domain_url'];
    }

   

    public function forceLogout()
    {
        session()->forget('login');
        unset($_COOKIE['_ndoptor']);
        $return_url = url('/login');
        return redirect(config('jisf.logout_sso_url') . '?referer=' . base64_encode($return_url));
    }
}
