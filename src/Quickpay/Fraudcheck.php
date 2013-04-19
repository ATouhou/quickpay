<?php 
namespace Quickpay;

class Fraudcheck
{
	private $_fraud_fields;

	public function __construct()
	{			
		$this->_fraud_fields = array( 
			'fraud_remote_addr' => '',
			'fraud_http_accept' => '',
			'fraud_http_accept_language' => '',
			'fraud_http_accept_encoding' => '',
			'fraud_http_accept_charset' => '',
			'fraud_http_referer' => '',
			'fraud_http_user_agent' => '',
		);
		
		foreach( $this->_fraud_fields as $key => &$value )
		{
			$server_key = strtoupper(substr($key, 6));
			if( isset($_SERVER[$server_key]) && !empty($_SERVER[$server_key]) )
			{
				$value = $_SERVER[$server_key];
			}
		}		
	}
	
	public function get_fields()
	{
		return $this->_fraud_fields;	
	}
	
	public function __get( $key )
	{
		if( isset($this->_fraud_fields[$key]) )
		{
			return $this->_fraud_fields[$key];
		}
		
	}
}	