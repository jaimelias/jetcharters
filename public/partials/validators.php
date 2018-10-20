<?php 

class Jetcharters_Validators{
	
	public static function validate_recaptcha()
	{
		if((isset($_REQUEST['g-recaptcha-response']) || get_query_var('instant_quote')) && get_option('captcha_secret_key'))
		{				
			if(isset($_REQUEST['g-recaptcha-response']))
			{
				$response = $_REQUEST['g-recaptcha-response'];
			}
			if(get_query_var('instant_quote'))
			{
				$response = get_query_var('instant_quote');
			}
			
			$data = array();
			$data['secret'] = get_option('captcha_secret_key');
			$data['remoteip'] = $_SERVER['REMOTE_ADDR'];
			$data['response'] = sanitize_text_field($response);
			
			write_log(json_encode($data));
			$url = 'https://www.google.com/recaptcha/api/siteverify';			
			$verify = curl_init();
			curl_setopt($verify, CURLOPT_URL, $url);
			curl_setopt($verify, CURLOPT_POST, true);
			curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
			curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
			$verify_response = json_decode(curl_exec($verify), true);
			
			if($verify_response['success'] == true)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}		
	}	

	public static function valid_jet_search()
	{
		if(get_query_var('instant_quote') && isset($_GET['jet_origin']) && isset($_GET['jet_destination']) && isset($_GET['jet_pax']) && isset($_GET['jet_flight']) && isset($_GET['jet_departure_date']) && isset($_GET['jet_departure_hour']) && isset($_GET['jet_return_date']) && isset($_GET['jet_return_hour']))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
}

?>