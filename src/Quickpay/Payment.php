<?php 
namespace Quickpay;

class Payment
{
	
	
	private $_guarded_request_fields;
	private $_secret;
	private $_fraud_check = null;
	
	public function __construct($merchant, $secret, $apikey = NULL)
	{
		$this->_secret = $secret;
		$this->_guarded_request_fields['protocol'] = 7;
		$this->_guarded_request_fields['merchant'] = $merchant;		
		$this->_guarded_request_fields['apikey'] = $apikey;
		
	}
	
	public function fraud_check( \Quickpay\Fraudcheck $fraudcheck )
	{		
		$this->_fraud_check = $fraudcheck;
	}
	
	public function execute( \Quickpay\Transaction $transaction )
	{
		$request_fields = $transaction->get_fields();
		if( ! is_null( $this->_fraud_check) )
		{
			$request_fields = array_merge($request_fields, $this->_fraud_check->get_fields());
		}
		
		$request_fields = $this->_build_data_fields( $request_fields );
		return $this->_query($request_fields);
				
	}
		
	public function html_form( \Quickpay\Transaction $transaction, $xhtml = false )
	{	
		$request_fields = $transaction->get_fields();
		if( ! is_null( $this->_fraud_check) )
		{
			$request_fields = array_merge($request_fields, $this->_fraud_check->get_fields());
		}
		
		$request_fields = $this->_build_data_fields( $request_fields, true );
		$html = '';
		$html_end = ($xhtml) ? ' />' : '>';		
		foreach($data_fields as $key => $value)
		{
			$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'"'.$html_end;
		}		
		return $html;
	}

	public function callback()
	{
		return $this->_response($_POST);				
	}

	/**
	* Calls the API
	* 
	* @param array $data_fields An array filled with nessecary information for making the request
	* @return object An object with response fields according to the documentation
	*/								
	private function _query($data_fields)
	{								
		$ch = curl_init();		
		curl_setopt($ch, CURLOPT_URL, 'https://secure.quickpay.dk/api');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POST, true);		
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_fields);
		
     	$content = curl_exec($ch);
     	curl_close($ch);
     	return $this->_response($content);     	
	}	
	
			
	/**
	* Builds the data request fields and makes sure they have the correct order and md5 checksum.
	* 
	* @param array $data_fields An array filled with nessecary information for making the request
	* @return array An array witht the sorted data and a md5 checksum.
	*/							
	private function _build_data_fields( $input_data, $html_form = false )
	{	
		
		$request_field_sorted = array(
			'protocol',
			'channel',
			'msgtype',
			'merchant',
			'language',
			'ordernumber',
			'amount',
			'currency',
			'continueurl',
			'cancelurl',
			'callbackurl',
			'autocapture',
			'autofee',
			'cardnumber',
			'expirationdate',
			'cvd',
			'mobilenumber',
			'smsmessage',
			'acquirer',
			'cardtypelock',
			'transaction',
			'description',
			'group',
			'splitpayment',
			'forcemobile',
			'deadline',
			'finalize',
			'cardhash',
			'testmode',
			'fraud_remote_addr',
			'fraud_http_accept',
			'fraud_http_accept_language',
			'fraud_http_accept_encoding',
			'fraud_http_accept_charset',
			'fraud_http_referer',
			'fraud_http_user_agent',
			'apikey',
			'md5check',
		);				
							
		
		// The final field array
		$data_fields = array();						
		foreach($request_field_sorted as $key)
		{
			if( $html_form && $key == "apikey" )
			{
				continue;	
			}
			
			// Is the key a reserved field?
			if(isset($this->_guarded_request_fields[$key] ))
			{
				$data_fields[$key] = $this->_guarded_request_fields[$key];
				continue;
			}
			
			if(isset($input_data[$key]))
			{
				$data_fields[$key] = $input_data[$key];
			}
		}
		
		
		$data_fields['md5check'] = md5(implode("", $data_fields).$this->_secret);
		return $data_fields;
	}			
		
	private function _response( $xml )
	{
		try
		{
			$xml = new \SimpleXMLElement($xml);
		}
		catch( \Exception $e)
		{}
		
		$response = new \stdClass();
		$md5string = '';
		foreach($xml as $key => $value)
		{			
			$response->{$key} = (string)$value;
			if($key != 'md5check' && $key != 'history')
			{
				$md5string .= (string)$value;
			}
		}
		
		if(isset($xml->history))
		{
			$response->history = array();
			foreach($xml->history as $history)
			{
				$obj = new stdClass();
				foreach($history as $key => $value)
				{
					$obj->{$key} = (string)$value;
				}
				$response->history[] = $obj;
			}			
		}
		
		// Make sure the data hasn't been tampered with.
		$response->is_valid = (md5($md5string . $this->_secret) == $response->md5check);
		return $response;
	}	
	
	
}