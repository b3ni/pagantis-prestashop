<?php

class TpvpagantisPaymentModuleFrontController extends ModuleFrontController
{

  public $display_column_left = true;

  public $display_column_right = false;

  /**
   * @see FrontController::initContent()
   */
  public function initContent()

  {

    parent::initContent();

    $cart = $this->context->cart;

    if (!$this->module->checkCurrency($cart))
      Tools::redirect('index.php?controller=order');
      $tpvpagantis = new TpvPagantis;
      $payment = $tpvpagantis->execPayment($cart);
      
    $this->context->smarty->assign(array(

      'nbProducts'        => $cart->nbProducts(),
      'cust_currency'     => $cart->id_currency,
      'currencies'        => $this->module->getCurrency((int)$cart->id_currency),
      'pagantis_endpoint' => $payment['pagantis_endpoint'],
      'account_id'        => $payment['account_id'],
      'currency'          => $payment['currency'],
      'ok_url'            => $payment['ok_url'],
      'nok_url'           => $payment['nok_url'],
      'order_id'          => $payment['order_id'],
      'amount'            => $payment['amount'],
      'description'       => $payment['description'],
      'signature'         => $payment['signature'],
      'customer'          => $payment['customer'],
      'total'             => $cart->getOrderTotal(true, Cart::BOTH),
      'this_path'         => $this->module->getPathUri(),
      'this_path_ssl'     => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'

    ));

    $this->setTemplate('payment_form.tpl');

  }

}