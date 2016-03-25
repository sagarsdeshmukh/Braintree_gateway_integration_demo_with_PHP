<?php
/**
 * Braintree - Payment Gateway Custom integration example
 * ==============================================================================
 * 
 * @version v1.0: braintree_custom_integration_demo.php 2016/03/25
 * @copyright Copyright (c) 2016, http://www.ilovephp.net
 * @author Sagar Deshmukh <sagarsdeshmukh91@gmail.com>
 * You are free to use, distribute, and modify this software
 * ==============================================================================
 *
 */

// Braintree library
require 'braintree/lib/Braintree.php';

$params = array(
	"testmode"   => "on",
	"merchantid" => "xxxxxxx",
	"publickey"  => "xxxxxxx",
	"privatekey" => "xxxxxxxxxxxxxxxxxxxxx",
);

if ($params['testmode'] == "on")
{
	Braintree_Configuration::environment('sandbox');
}
else
{
	Braintree_Configuration::environment('production');
}

Braintree_Configuration::merchantId($params["merchantid"]);
Braintree_Configuration::publicKey($params["publickey"]);
Braintree_Configuration::privateKey($params["privatekey"]);

if(isset($_POST['make_payment']))
{
	// Customer details
	$customer_firstname   = $_POST['c_firstname'];
	$customer_lastname    = $_POST['c_lastname'];
	$customer_email       = $_POST['c_email'];
	$customer_phonenumber = $_POST['c_phonenumber'];
	// EOF Customer details

	// Customer billing details
	$firstname = $_POST['firstname'];
	$lastname  = $_POST['lastname'];
	$email     = $_POST['email'];
	$address1  = $_POST['address1'];
	$address2  = $_POST['address2'];
	$city      = $_POST['city'];
	$state     = $_POST['state'];
	$postcode  = $_POST['postcode'];
	$country   = $_POST['country'];
	$phone     = $_POST['phonenumber'];
	// EOF Customer billing details

	// Credit Card Details
	$card_number = $_POST['card_number'];
	$cvv         = $_POST['cvv'];
	$exp_date    = explode("/",$_POST['exp_date']);
	// EOF Credit Card Details

	// Create customer in braintree Vault
	$result = Braintree_Customer::create(array(
		'firstName' => $customer_firstname,
		'lastName'  => $customer_lastname,
		'phone'     => $customer_phonenumber,
		'email'     => $customer_email,
		'creditCard' => array(
			'number'          => $card_number,
			'cardholderName'  => $firstname . " " . $lastname,
			'expirationMonth' => $exp_date[0],
			'expirationYear'  => $exp_date[1],
			'cvv'             => $cvv,
			'billingAddress' => array(
				'postalCode'        => $postcode,
				'streetAddress'     => $address1,
				'extendedAddress'   => $address2,
				'locality'          => $city,
				'region'            => $state,
				'countryCodeAlpha2' => $country
			)
		)
	));

	if ($result->success) {
		// Save this Braintree_cust_id in DB and use for future transactions too
		$braintree_cust_id = $result->customer->id; 
	} else {
		die("Error : ".$result->message);
	}
	// EOF Create customer in braintree Vault

	$sale = array(
				'customerId' => $braintree_cust_id,
				'amount'   => $_POST['amount'],
				'orderId'  => $_POST['invoiceid'],
				'options' => array('submitForSettlement'   => true)
			);
						
	$result = Braintree_Transaction::sale($sale);

	if ($result->success)
	{
		// Execute on payment success event at here
	}
	else
	{
		echo "Error : ".$result->_attributes['message'];
	}
	
	print_r($result); exit;
}
else
if (isset($_POST['braintree_cust_id']))
{
	$sale = array(
				'customerId' => $braintree_cust_id,
				'amount'     => $_POST['amount'],
				'orderId'    => $_POST['invoiceid'],  // This field is get back in responce to track this transaction
				'options'    => array('submitForSettlement' => true)
			);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Checkout</title>
</head>
<body>
<link href="style.css" type="text/css" rel="stylesheet" />
<h1 class="bt_title">Braintree Custom Integration</h1>
<div class="dropin-page">
  <form id="checkout" method="post" action="">
    <h4 class="bt_title">Customer Information</h4>
    <input type="hidden" name="invoiceid" value="123456">
     <fieldset class="one_off_firstname">
      <label class="input-label" for="firstname">
      <span class="field-name">First Name</span>
      <input id="c_firstname" name="c_firstname" class="input-field card-field" type="text" placeholder="First Name" autocomplete="off">
      <div class="invalid-bottom-bar"></div>
      </label>
    </fieldset>
    <fieldset class="one_off_lastname">
      <label class="input-label" for="lastname">
      <span class="field-name">Last Name</span>
      <input id="c_lastname" name="c_lastname" class="input-field card-field" type="text" placeholder="Last Name" autocomplete="off">
      <div class="invalid-bottom-bar"></div>
      </label>
    </fieldset>
    <fieldset class="one_off_lastname">
      <label class="input-label" for="email">
      <span class="field-name">Email</span>
      <input id="c_email" name="c_email" class="input-field card-field" type="text" placeholder="Email" autocomplete="off">
      <div class="invalid-bottom-bar"></div>
      </label>
    </fieldset>
    <fieldset class="one_off_phonenumber">
      <label class="input-label" for="phonenumber">
      <span class="field-name">Phone Number</span>
      <input id="c_phonenumber" name="c_phonenumber" class="input-field card-field" type="text"placeholder="Phone Number" autocomplete="off">
      <div class="invalid-bottom-bar"></div>
      </label>
    </fieldset>
    <h4 class="bt_title">Customer Billing Information</h4>
    <fieldset class="one_off_firstname">
      <label class="input-label" for="firstname">
      <span class="field-name">First Name</span>
      <input id="firstname" name="firstname" class="input-field card-field" type="text" placeholder="First Name" autocomplete="off">
      <div class="invalid-bottom-bar"></div>
      </label>
    </fieldset>
    <fieldset class="one_off_lastname">
      <label class="input-label" for="lastname">
      <span class="field-name">Last Name</span>
      <input id="lastname" name="lastname" class="input-field card-field" type="text" placeholder="Last Name" autocomplete="off">
      <div class="invalid-bottom-bar"></div>
      </label>
    </fieldset>
    <fieldset class="one_off_address1">
      <label class="input-label" for="address1">
      <span class="field-name">Address1</span>
      <input id="address1" name="address1" class="input-field card-field" type="text" placeholder="Address" autocomplete="off">
      <div class="invalid-bottom-bar"></div>
      </label>
    </fieldset>
    <fieldset class="one_off_address2">
      <label class="input-label" for="address2">
      <span class="field-name">Address2</span>
      <input id="address2" name="address2" class="input-field card-field" type="text" placeholder="Address" autocomplete="off">
      <div class="invalid-bottom-bar"></div>
      </label>
    </fieldset>
    <fieldset class="one_off_city">
      <label class="input-label" for="city">
      <span class="field-name">City/Town</span>
      <input id="city" name="city" class="input-field card-field" type="text" placeholder="City/Town" autocomplete="off">
      <div class="invalid-bottom-bar"></div>
      </label>
    </fieldset>
    <fieldset class="one_off_state">
      <label class="input-label" for="state">
      <span class="field-name">State/Region</span>
      <input id="state" name="state" class="input-field card-field" type="text" placeholder="State/Region" autocomplete="off">
      <div class="invalid-bottom-bar"></div>
      </label>
    </fieldset>
    <fieldset class="one_off_postcode">
      <label class="input-label" for="postcode">
      <span class="field-name">Post Code</span>
      <input id="postcode" name="postcode" class="input-field card-field" type="text" placeholder="Post Code" autocomplete="off">
      <div class="invalid-bottom-bar"></div>
      </label>
    </fieldset>
    <fieldset class="one_off_country">
      <label class="input-label" for="country">
      <span class="field-name">Country</span>
      <input id="country" name="country" class="input-field card-field" type="text" placeholder="Country" autocomplete="off">
      <div class="invalid-bottom-bar"></div>
      </label>
    </fieldset>
    <h4 class="bt_title">Credit Card Details</h4>
    <fieldset class="one_off_country">
      <label class="input-label" for="country">
      <span class="field-name">Card number</span>
      <input id="card_number" name="card_number" class="input-field card-field" type="text" placeholder="Card number" autocomplete="off">
      <div class="invalid-bottom-bar"></div>
      </label>
    </fieldset>
    <fieldset class="one_off_country">
      <label class="input-label" for="country">
      <span class="field-name">CVV</span>
      <input id="CVV" name="cvv" class="input-field card-field" type="text" placeholder="CVV" autocomplete="off">
      <div class="invalid-bottom-bar"></div>
      </label>
    </fieldset>
    <fieldset class="one_off_country">
      <label class="input-label" for="country">
      <span class="field-name">Expiration date (MM/YY)</span>
      <input id="exp_date" name="exp_date" class="input-field card-field" type="text" placeholder="Expiration date (MM/YY)" autocomplete="off">
      <div class="invalid-bottom-bar"></div>
      </label>
    </fieldset>
    <fieldset class="one_off_amount">
      <label class="input-label" for="amount">
      <span class="field-name">Amount</span>
      <input id="amount" name="amount" class="input-field card-field" type="number" inputmode="numeric" placeholder="Amount" autocomplete="off" step="any">
      <div class="invalid-bottom-bar"></div>
      </label>
    </fieldset>
    <div class="btn_container">
      <input type="submit" name="make_payment" value="Make Payment" class="pay-btn">
      <span class="loader_img"></span> </div>
  </form>
</div>
</body>
</html>