<?php

namespace App\Traits;

use App\Models\OfficeDesignationRole;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;

trait OfficeInfoCollector
{
    use NdoptorToken;

    	
	public static $ndoptor_api_list = [
		'OFFICES' => 'api/offices',
		'MINISTRY_LAYERS' => 'api/ministry/layers/',
		'USER_VERIFY' => 'api/user/verify',
		'CLIENT_LOGIN' => 'api/client/login',
		'OFFICE_UNIT_DESIGNATION_EMPLOYEE_MAP' => 'api/office/unit-designation-employee-map',
		'MINISTRIES' => 'api/ministries/',
		'USER_SIGNATURE' => 'api/user/signatures/',
		'MINISTRY_LAYER_OFFICES' => 'api/ministry/layer/offices/',
		'OFFICE_RELATIONAL_OFFICES' => 'api/offices/relation-offices',
		'OFFICE_UNITS' => 'api/office/units/',
		'USER_DESIGNATIONS' => 'api/user/designations/',
		'GEO_DIVISIONS' => 'api/geo/divisions/',
		'GEO_DISTRICTS' => 'api/geo/districts/',
		'GEO_UPAZILLAS' => 'api/geo/upazillas/',
		'GEO_UNIONS' => 'api/geo/unions/',
		'UNIT_HEAD_ADMIN' => 'api/office/unit-head-and-admin',
		'OFFICE_HEAD_ADMIN' => 'api/office/head-and-admin',
		'EMPLOYEE_INFORMATION' => 'api/user/employee/information',

	];

   //public static $api_domain =; 'https://n-doptor-api.nothi.gov.bd/'; // Live Nothi Url

  

    public static function curlResponse($uri, $method = 'GET', $data = [], $headers = []) {
        $headers = array_merge($headers, ['api-version' => 1]);
        
        foreach ($headers as $key => &$header) {
        	$header = $key . ': ' . $header;
		}
		$headers = array_values($headers);

       
 
		$endpoint = 'https://api-stage.doptor.gov.bd/' . self::$ndoptor_api_list[$uri];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => $headers,
        ));

        $apiResponse = curl_exec($curl);

        curl_close($curl);
        return  json_decode((string) $apiResponse);
    }


    public static function getResponse($uri, $method = 'POST', $data = [],$forTerminal=false)
    {
      
        $token = NdoptorToken::getToken();
  
        $response = self::curlResponse($uri, $method, $data, [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ]);

        if (isset($response->status) && $response->status == 'success') {
            return $response->data;
        } else {
            return $response;
        }
    }
 
    public static function getOfficeDesignations(Request $request,$forTerminal=false) {
    	$office_designations = collect(self::getResponse( 'OFFICE_UNIT_DESIGNATION_EMPLOYEE_MAP', 'POST', ['office_id' => $request->office_id],$forTerminal));

    	$office_designations = json_decode(json_encode($office_designations));
		 
    	return $office_designations;
    }

    public static function getOfficeAdmin($office_id,$forTerminal=false) {
        $request = new Request();
        $request->merge([
            'office_id' => $office_id
        ]);
        $office_designations = self::getOfficeDesignations($request,$forTerminal);
        $office_designations = json_decode(json_encode($office_designations), true);

        $office_admin = [];
        if ($office_designations && isset($office_designations[$office_id])) {

            foreach ($office_designations[$office_id]['units'] as $unit) {
				$found_key = array_search(true, array_column($unit['designations'], 'is_admin'));
				if ($found_key !== false) {
					$designations = array_values($unit['designations']);
					//dd($designations[$found_key], $found_key);

					$office_admin = $designations[$found_key];
					$office_admin['unit'] = Arr::except($unit, 'designations');
					break;
				}
                /*$designations = array_column($unit['designations'], null, 'is_admin');
                if (isset($designations[1])) {
                    $office_admin = $designations[1];
                    $office_admin['unit'] = Arr::except($unit, 'designations');
                    break;
                }*/
            }
        }

        return $office_admin;
    }



    public function getOfficeHeadAndAdmin($office_id) {
		
		$office_head_and_admin = collect(self::getResponse( 'OFFICE_HEAD_ADMIN', 'POST', ['office_id' => $office_id]));
	    session(['office_head_and_admin_' . $office_id => base64_encode($office_head_and_admin)]);
		return $office_head_and_admin;
	}

    public function getUnitHeadAndAdmin($unit_id) {
		$unit_head_and_admin = session('unit_head_and_admin_' . $unit_id);
		if ($unit_head_and_admin) {
			return json_decode(base64_decode($unit_head_and_admin));
		}

		$unit_head_and_admin = collect(self::getResponse( 'UNIT_HEAD_ADMIN', 'POST', ['unit_id' => $unit_id]));
		session(['unit_head_and_admin_' . $unit_id => base64_encode($unit_head_and_admin)]);
		return $unit_head_and_admin;
	}

    
    public static function getAllOfficeData($office_id=null){
        $allOfficeData = session('allOfficeData');
		if ($allOfficeData) {
			return json_decode(base64_decode($allOfficeData));
		}

		$allOfficeData = collect(self::getResponse('OFFICES', 'POST', $office_id));
        session(['allOfficeData'=> base64_encode($allOfficeData)]);

		return $allOfficeData;
    }


    
    public function getDesignationWithName($officeId)
    {
         
        $office_id =$officeId;

        $office_designations = collect($this->getResponse( 'OFFICE_UNIT_DESIGNATION_EMPLOYEE_MAP', 'POST', ['office_id' => $office_id]));

        $office_designations = json_decode(json_encode($office_designations));

       

        $data = [];

        if($office_designations){

            foreach ($office_designations->$office_id->units as $unit_id => $unit) {
                //dd($unit->unit_name_bng);
                 
                foreach ($unit->designations as $designation_id => $designation) {

                    $data['officeUnit'][$unit_id]=$unit->unit_name_bng;

                    if(!empty($designation->employee_info)  && $designation->employee_info->name_bng){

                        $data['designationName'][$designation_id]=$designation->employee_info->name_bng.','.$designation->designation_bng;

                    }

                 
                }  
                
               // dd($data);
            }
        }
        
       // dd($data);

      
      
    }

}
