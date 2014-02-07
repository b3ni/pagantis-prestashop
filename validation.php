<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/tpvpagantis.php');

$tpvpagantis = new TpvPagantis();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $json = file_get_contents('php://input');
  $data = json_decode($json, true);
  
  if ($data["event"] == 'charge.created'){
    $order_id = $data["data"]["order_id"];
    $amount   = $data["data"]["amount"];
    $currency = $data["data"]["currency"];
    $auth     = $data["data"]["id"];
    
    $amount_paid = round(floatval($amount/100), 2);
    
    $cart = new Cart($order_id);
    $customer = new Customer((int)$cart->id_customer);
        
    $tpvpagantis->validateOrder((int)$order_id, 
                                (int)Configuration::get('PS_OS_PAYMENT'), //id_order_state
                                $amount_paid, 
                                $tpvpagantis->displayName, //payment_method
                                $tpvpagantis->l('Payment received. Auth code: ').$auth.'<br>', //message
                                array(), null, false, $customer->secure_key);

    echo '$*$OKY$*$';
  }

} else {
 echo "$*NOK$*$";
}
?>
