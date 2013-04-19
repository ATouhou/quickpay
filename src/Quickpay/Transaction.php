<?php 
namespace Quickpay;

class Transaction
{
	private $_data_fields;
	
	public function __construct()
	{
		$this->_data_fields = array(
		'channel' => 'creditcard',	
		'msgtype' => 'authorize',	
		'language' => '', // Form only
		'ordernumber' => '',  // Form and API
		'amount' => '',  // Form and API
		'currency' => '',  // Form and API
		'continueurl' => '', // Form only
		'cancelurl' => '', // Form only
		'callbackurl' => '', // Form only 
		'autocapture' => 0,  // Form and API
		'autofee' => '', // Form only
		'cardnumber' => '',
		'expirationdate' => '',
		'cvd' => '',
		'mobilenumber' => '',
		'smsmessage' => '',
		'acquirer' => '',
		'cardtypelock' => '', 
		'transaction' => '',
		'description' => '',
		'group' => '', //Form only
		'splitpayment' => '',
		'forcemobile' => '', // Form only
		'deadline' => '',// Form only
		'finalize' => '',
		'cardhash' => 1,
		'testmode' => 0,
		);														
	}
	
	public function __call($name, $arguments)
	{
		
		
		if( substr($name, 0, 3) == "set" )
		{
			$key = strtolower(substr($name, 3));

			if( array_key_exists($key, $this->_data_fields) )
			{
				$this->_data_fields[$key] = $arguments[0];					
				return;
			}
			else
			{
				throw new \Exception("{$key} doesn't exist in ".__CLASS__);
			}
									
		}
		else
		{
			if( method_exists( $this, $name)  )
			{
				return call_user_func_array( array($this, $name), $arguments );
			}	
		}
		
		throw new \Exception("'{$name}' method is not supported in ".__CLASS__);
		
	}		
    
    public function get_fields()
    {
    	$request_data = array();
    	
    	foreach($this->_data_fields as $key => $value)
    	{    		
    		if( empty($value) )
    		{
    			continue;	
    		}
    		    		
    		$request_data[$key] = $value;    		
    	}
    	
    	return $request_data;    	
    }
    
    
    private function _test_regexp($pattern, $value, $key)
    {
    	if( ! preg_match($pattern, $value) )
    	{
    		throw new \Exception("{$key}: '{$value}' does not match the regular expression {$pattern}");
    	}    
    }
    
}