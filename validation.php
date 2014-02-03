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
    
    $tpvpagantis->validateOrder((int)$order_id, 
                                (int)Configuration::get('PS_OS_PAYMENT'), //id_order_state
                                $amount_paid, 
                                $tpvpagantis->displayName, //payment_method
                                $tpvpagantis->l('Payment received. Auth code: ').$auth.'<br>' //message
                                );

    echo '$*$OKY$*$ -- '.$data["event"].'--'.$order_id.'--'.$amount.'--'.$currency.'--'.$auth.'--'.$amount_paid; 
  }

} else {
 echo "$*NOK$*$";
}
?>
