====================
QuickPay API Library
====================

This library will enable you to communicate with the [QuickPay API](http://quickpay.net/features/api/).<br>
Documentation can be found here: <http://doc.quickpay.net/api.html>


Usage
-----

To use the object, just include the QuickPay object file and create an instance.<br>
For creating an instance, make sure you have your QuickPay ID and MD5 secret code ready.<br>
If you haven't whitelisted your servers IP through the [QuickPay manager](https://manager.quickpay.net/), you will need your API key too.

Amounts for all transactions is written in its smallest unit. For example, 1 EUR is written 100

<!-- language-all: lang-php -->
	include 'quickpay.php';
	
	// This instantiation example assumes that you have whitelisted your servers IP.
	$qp = new Quickpay(11111111, '569ef72642be0fadd711d6a468d68ee1d6b3c0ad82178d0242a6b36339051ca2');

	// This instantiation example assumes that you have NOT whitelisted your servers IP and need to make use of the API key as the third parameter.
	$qp = new Quickpay(11111111, '569ef72642be0fadd711d6a468d68ee1d6b3c0ad82178d0242a6b36339051ca2', '3c0ad82182178d0242a69051c[...]b78d0242a6b3633');

Enabling testmode
-----------------

This is really simple. As soon as you have created an instance of the QuickPay object, just do the following:

	$qp->testmode(TRUE);

Message Types
-------------

Assuming that you already created an instance of the QuickPay object, you can now contact the API with all available message types.

***Warning!***<br>
You are only allowed to do authorizes and subscribes through the QuickPay API if your setup has passed the full PCI certification. Please use the QuickPay Payment Window instead.<br>
Please refer to the bottom of this document to see uses with the QuickPay Payment Window.

**Message type: authorize**

This message type is used when the merchant wants to validate refund card data against the card issuer and authorize a transaction. The transaction amount is only reserved at the card holder's account and not withdrawn from the account - unless the autocapture field is set to TRUE.

`Quickpay::authorize( $ordernumber, $amount, $currency, $cardnumber, $expirationdate, $cvd[, $autocapture = FALSE] )`

Example:

	// Authorize a payment and reserve the amount for later capture
	$response = $qp->authorize($unique_ordernumber, '100', 'EUR', '4571123412341234', '0912', '123');
	
	// Authorize a payment and reserve the amount and capture it immediatly (third parameter set to TRUE
	$response = $qp->authorize($unique_ordernumber, '100', 'EUR', '4571123412341234', '0912', '123', TRUE);	
	
**Message type: subscribe**

Like the message type authorize, this message type is used when the merchant wants to validate refund card data against the card issuer. When the merchant wants to make a withdrawal from the subscription, the id from this transaction is used as a reference for message type recurring.

`Quickpay::subscribe( $ordernumber, $description, $cardnumber, $expirationdate, $cvd )`

Example:

	// Add a subscription to a refund card
	$response = $qp->subscribe(time(), 'Something', '4571123412341234', '0912', '123');
	
**Message type: recurring**

This message type is used when the merchant wants to make a withdrawal from a subscription. The transaction amount is only reserved at the card holder's account and not withdrawn from the account - unless the autocapture field is set to TRUE.

`Quickpay::recurring( $ordernumber, $amount, $currency, $transaction, [$autocapture = FALSE] )`

Example:

	// Reserve a amount to capture later
	$response = $qp->recurring($unique_ordernumber, 1000, 'EUR', '21451214' );
	
	// Reserve a amount to autocapture by setting the 5th parameter to TRUE
	$response = $qp->recurring($unique_ordernumber, 1000, 'EUR', '21451214', TRUE );

**Message type: cancel**

This message type is used when the merchant wants to cancel the order. A cancellation will delete the reservation on the cardholders account.

`Quickpay::cancel( $transaction )`

Example:

	// Cancel a transaction
	$response = $qp->cancel('21451214');
	
**Message type: renew**

This message type is used when the merchant wants to renew an authorized transaction.

`Quickpay::renew( $transaction )`

Example:

	// Renew a transaction
	$response = $qp->renew('21451214');
		
		
**Message type: capture**

This message type is used when the merchant wants to transfer part of or the entire transaction amount from the cardholders account.

`Quickpay::capture($transaction, $amount, [$finalize = FALSE])`

Example:

	// Capture an amount for a transaction
	$response = $qp->recurring('21451214', 1000);
	
	// Capture an amount for a transaction and finalize the transctions (no more captures can done) by setting the third paramater to TRUE.
	$response = $qp->recurring('21451214', 1000, TRUE);
	
**Message type: refund**

This message type is used when the merchant wants to renew an authorized transaction.

`Quickpay::refund($transaction, $amount)`

Example:

	// This message type is used when the merchant wants to transfer part of or the entire transaction amount to the cardholders account.
	$response = $qp->refund('21451214', 1000);
	
		
**Message type: status**

This message type is used when the merchant wants to check the status of a transaction. The response from this message type differs from the others as it contains the history of the transaction as well.

`Quickpay::status( $transaction )`

Example:

	// Get status and transaction history for a transaction ID
	$response = $qp->status('21451214');
		
	// Get status and transaction history for a transaction from the ordernumber
	$response = $qp->status_from_order('1234');		
	
The Response
------------

Handling the response will be explained further down.

The `$response` variable from the examples contains and object with the following public members:

 - msgtype - *Defines which action was performed*
 - ordernumber - *A value specified by merchant in the initial request.*
 - amount - *The amount defined in the request in its smallest unit. In example, 1 EUR is written 100.*
 - balance - *Total amount captured. Only present on status request*
 - currency - *The transaction currency as the 3-letter ISO 4217 alphabetical code. See <http://quickpay.net/features/multi-currency/> for more information.*
 - time - *The time of which the message was handled. Format is YYMMDDHHIISS.*
 - state - *The current state of the transaction. See <http://quickpay.net/faq/transaction-states/>.*
 - qpstat - *Return code from QuickPay. See <http://quickpay.net/faq/status-codes/>.*
 - qpstatmsg - *A message detailing errors and warnings if any.*
 - chstat - *Return code from the clearing house. Please refer to the acquirers documentation.*
 - chstatmsg - *A message from the clearing house detailing errors and warnings if any.*
 - merchant - *The QuickPay merchant name*
 - merchantemail - *The QuickPay merchant email/username.*
 - transaction - *The id assigned to the current transaction.*
 - cardtype - *The card type used to authorize the transaction.*
 - cardnumber - *A truncated version of the card number - eg. 'XXXX XXXX XXXX 1234'. Note: This field will be empty for other message types than 'authorize' and 'subscribe'.*
 - cardexpire - *Expire date on the card used in a 'subscribe'. Notation is 'yymm'. Note: This field will be empty for other message types than 'subscribe'.*
 - splitpayment - *Tells if the transaction has the split payment feature enabled.*
 - fraudprobability - *Fraud probability if fraudcheck was done*
 - fraudremarks - *Fraud remarks if fraudcheck was done*
 - fraudreport - *Fraud report if reported as fraud*
 - md5check - *A MD5 checksum to ensure data integrity. See http://quickpay.net/faq/md5check/ for more information.*
 - is_valid - *Contains a boolean which indicates whether or not the response is valid and untampered with.*
 
**Note:**<br>
For status request, another member for the response object is present named *history* which contains an array with objects containing these members:
 - msgtype
 - amount
 - state
 - time
 - qpstat
 - qpstatmsg
 - chstat
 
Handling the response
---------------------

The simple way to handle the response from your request would to make sure that the return code (`qpstat`) equals to '000' AND that the request is valud (`is_valid`). Like so:

	if( $response->qpstat == '000' && $response->is_valid )
	{
		// The response is valid and the request was approved.
	}
	else
	{
		//Something went wrong
		var_dump($response->qpstatmsg);
		var_dump($response->chstat);
		var_dump($response->chstatmsg);
	}
	
QuickPay Payment Window
-----------------------

To avoid the PCI certification, you should use the QuickPay Payment Window solution.

See all available fields here: <http://doc.quickpay.net/paymentwindow/technicalspecification.html#index1h2>

A simple helper is also implemented for creating the nessecary fields AND creating the md5 checksum.<br>
See this example:

	<?php
	
		include 'quickpay.php';		
		$qp = new Quickpay(11111111, '569ef72642be0fadd711d6a468d68ee1d6b3c0ad82178d0242a6b36339051ca2');
		
		$data_fields['msgtype'] = 'authorize';	
		$data_fields['language'] = 'en';	
		$data_fields['ordernumber'] = time();	
		$data_fields['amount'] = '100';	
		$data_fields['currency'] = 'EUR';
		$data_fields['continueurl'] = 'http://quickpay.net/features/payment-window/ok.php';
		$data_fields['cancelurl'] = 'http://quickpay.net/features/payment-window/error.php';
		$data_fields['callbackurl'] = 'http://quickpay.net/features/payment-window/callback.php';
	
	?>
	<form action="https://secure.quickpay.dk/form/" method="post">
		<?php echo $qp->form_fields($data_fields); ?>
	<input type="submit" value="Open Quickpay payment window" />
	
This create the form with the necessary input fields and pushing the submit button will open the QuickPay Payment window.

For the callback provided in the callbackurl field, you can get the same response object as used in the API:

	<?php
	
		include 'quickpay.php';		
		$qp = new Quickpay(11111111, '569ef72642be0fadd711d6a468d68ee1d6b3c0ad82178d0242a6b36339051ca2');
		
		$response = $qp->callback();	
		foreach($response as $key => $value)
		{
			$message .= "{$key}: {$value}\r\n";
		}
		mail('you@example.com', 'callbackurl', $message);		

	?>
	
The last example handles the response as mails you the data recieved.<br>
To validate the response, do as stated in earlier like mentioned in the API solution.