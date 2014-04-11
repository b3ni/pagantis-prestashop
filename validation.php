<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/tpvpagantis.php');

if (empty(Context::getContext()->link))
  Context::getContext()->link = new Link();

$tpvpagantis = new TpvPagantis();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $json = file_get_contents('php://input');
  $data = json_decode($json, true);
  
  $order_id = $data["data"]["order_id"];
  $cart_id = substr($order_id, 5);
  $amount   = $data["data"]["amount"];
  $currency = $data["data"]["currency"];
  $auth     = $data["data"]["id"];
  
  if ($data["event"] == 'charge.created'){
    
    $amount_paid = round(floatval($amount/100), 2);
    
    $cart = new Cart($cart_id);
    $customer = new Customer((int)$cart->id_customer);
        
    $tpvpagantis->validateOrder((int)$cart_id, 
                                (int)Configuration::get('PS_OS_PAYMENT'), //id_order_state
                                $amount_paid, 
                                $tpvpagantis->displayName, //payment_method
                                $tpvpagantis->l('Payment received. Details: ').'<a href="https://bo.pagantis.com/charges/'.$auth.'">'.$auth.'</a><br>', //message
                                array(), null, false, $customer->secure_key);
                                
  }
  echo '$*$OKY$*$';

} else {
 echo "$*NOK$*$";
}
?>
