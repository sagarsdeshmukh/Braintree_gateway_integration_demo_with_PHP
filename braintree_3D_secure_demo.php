<?php
/**
 * Braintree - Payment Gateway integration with 3D secure example
 * ==============================================================================
 * 
 * @version v1.0: braintree_3D_secure_demo.php 2016/04/25
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

$braintree_cust_id = "xxxxxxx";

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

if(isset($_POST['make_payment']) && $_POST['make_payment'])
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

	$payment_method_nonce = "";
	$enable_3d_flag = ""; // 3D secure is apply only for credit card , if payment method is PayPal then skip it
	if(isset($_POST['payment_method_nonce']))
	{
		$payment_method_nonce = $_POST['payment_method_nonce'];
		$enable_3d_flag = 1;
	}
	else
	{
		$payment_method_details = get_default_payment_method($braintree_cust_id);
		$bt_result = Braintree_PaymentMethodNonce::create($payment_method_details["token"]);
		$payment_method_nonce = $bt_result->paymentMethodNonce->nonce;
		$enable_3d_flag = 0;
	}

	$sale = array(
				'amount'   => $_POST['amount'],
				'orderId'  => $_POST['invoiceid'],
				'paymentMethodNonce' => $payment_method_nonce,
				'customer' => array(
								'firstName' => $customer_firstname,
								'lastName'  => $customer_lastname,
								'phone'     => $customer_phonenumber,
								'email'     => $customer_email
							  ),
				'billing' => array(
								'firstName'         => $firstname,
								'lastName'          => $lastname,
								'streetAddress'     => $address1,
								'extendedAddress'   => $address2,
								'locality'          => $city,
								'region'            => $state,
								'postalCode'        => $postcode,
								'countryCodeAlpha2' => $country
							 ),
				'options' => array(
								'submitForSettlement'   => true,
								'storeInVaultOnSuccess' => true
							 )
			);
			
	if ($enable_3d_flag == 1)
	{
		$sale["options"]["three_d_secure"] = array('required' => true);
	}
						
	$result = Braintree_Transaction::sale($sale);
	if ($result->success)
	{
		echo "Braintree_cust_id : ".$braintree_cust_id = $result->transaction->_attributes['customer']['id'];
	}
	else
	{
		echo "Error : ".$result->_attributes['message'];
	}
	
	print_r($result); exit;
}
else
{
	$clientToken = Braintree_ClientToken::generate(array(
		 'customerId' => $braintree_cust_id
	));

	$payment_method_details = get_default_payment_method($braintree_cust_id);
	
	$bt_payment_method_nonce = "";
	
	if ($payment_method_details["is_pp_account"] != true)
	{
		$bt_result = Braintree_PaymentMethodNonce::create($payment_method_details["token"]);
		$bt_payment_method_nonce = $bt_result->paymentMethodNonce->nonce;
	}
}

function get_default_payment_method($braintree_cust_id)
{
	$bt_customer = Braintree_Customer::find($braintree_cust_id);
	$payment_method_details = array();
	
	foreach($bt_customer->creditCards as $key => $card_obj)
	{
		if ($card_obj->default)
		{
			$payment_method_details["imageUrl"] = $card_obj->imageUrl;
			$payment_method_details["cardType"] = $card_obj->cardType;
			$payment_method_details["last2"] = substr($card_obj->last4,2,2);
			$payment_method_details["expirationDate"] = $card_obj->expirationMonth."/".substr($card_obj->expirationYear,2,2);
			$payment_method_details["token"] = $card_obj->token;
			$payment_method_details["is_pp_account"] = false;
		}
	}
	
	foreach($bt_customer->paypalAccounts as $key => $card_obj)
	{
		if ($card_obj->default)
		{
			$payment_method_details["imageUrl"] = $card_obj->imageUrl;
			$payment_method_details["email"] = $card_obj->email;
			$payment_method_details["token"] = $card_obj->token;
			$payment_method_details["is_pp_account"] = true;
		}
	}

	return $payment_method_details;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Braintree : Checkout</title>
</head>
<body>
<link href="style.css" type="text/css" rel="stylesheet" />
<h1 class="bt_title">Braintree 3D Secure Integration Demo</h1>
<div class="dropin-page">
<form id="checkout" method="post" action="" onSubmit="verify_3d_secure(); return false;">
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
  <fieldset style="margin-left: 220px;">
      <?php if ($payment_method_details["is_pp_account"]) { ?>
      <div align="center">
        <div style="float:left"> <img src="<?php echo $payment_method_details["imageUrl"] ?>" alt="PayPal" /> </div>
        <div style="float:left; margin-left: 10px;margin-top:7px"> <b><?php echo $payment_method_details["email"] ?></b> </div>
      </div>
      <?php } else { ?>
      <div align="center">
        <div style="float:left"> <img src="<?php echo $payment_method_details["imageUrl"] ?>" alt="<?php echo $payment_method_details["cardType"] ?>" /> </div>
        <div style="float:left; margin-left: 10px; margin-top:5px"> <b><?php echo $payment_method_details["cardType"] ?></b> <span style="color:#999">ending in <?php echo $payment_method_details["last2"] ?></span><br />
          <span style="color:#999">Expires <?php echo $payment_method_details["expirationDate"] ?></span> </div>
      </div>
      <?php } ?>
  </fieldset>
  <fieldset class="one_off_amount">
    <label class="input-label" for="amount">
    <span class="field-name">Amount</span>
    <input id="amount" name="amount" class="input-field card-field" type="number" inputmode="numeric" placeholder="Amount" autocomplete="off" step="any" required>
    <div class="invalid-bottom-bar"></div>
    </label>
  </fieldset>
  <div class="btn_container">
    <input type="hidden" name="make_payment" value="1">
    <input type="submit" value="Make Payment" class="pay-btn">
    <span class="loader_img"></span> </div>
</form>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script> 
<script src="https://js.braintreegateway.com/v2/braintree.js"></script> 
<!-- TO DO : Place below JS code in js file and include that JS file --> 
<script type="text/javascript">
  function verify_3d_secure()
  {
    var is_pp_account = "<?php echo $payment_method_details["is_pp_account"] ?>"; // check payment method is PayPal

    if (is_pp_account != true)
    {
        var client = new braintree.api.Client({
          clientToken: "<?php echo $clientToken; ?>"
        });

        client.verify3DS({
          amount: $("#amount").val(),
          creditCard: "<?php echo $bt_payment_method_nonce; ?>"
        }, function (error, response) {
          // Handle response
          if (!error) {
            // 3D Secure finished. Using response.nonce you may proceed with the transaction with the associated server side parameters below.
            appendTo(document.forms.checkout, 'input', {name: 'payment_method_nonce', type: 'hidden', value: response.nonce});
            document.forms.checkout.submit();
          } else {
            // Handle errors
            console.log("Error :"+error.message);
          }
        });
    }
    else
    {
      document.forms.checkout.submit();
    }
  }

  function appendTo($cont, childSelector, options)
  {
	var input = document.createElement(childSelector);
	input.type = options.type;
	input.name = options.name;
	input.value = options.value;
	$cont.appendChild(input);
  };
</script>
</body>
</html>