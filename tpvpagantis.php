<?php

if (!defined('_PS_VERSION_'))
  exit;

class TpvPagantis extends PaymentModule
{
  
  private $_html = '';
  private $_postErrors = array();

  public $details;
  public $owner;
  public $address;

  public function __construct()
  {
    $this->name = 'tpvpagantis';
    $this->tab = 'payments_gateways';
    $this->version = '1.0.0';
    $this->author = 'Pagantis.com';
    
    $this->currencies = true;
    $this->currencies_mode = 'checkbox';

    $config = Configuration::getMultiple(array('TPV_PAGANTIS_DETAILS', 'TPV_PAGANTIS_OWNER', 'TPV_PAGANTIS_ADDRESS',
                                               'TPV_PAGANTIS_URL', 
                                               'TPV_PAGANTIS_ACCOUNT_ID_TEST', 'TPV_PAGANTIS_ACCOUNT_ID_LIVE',
                                               'TPV_PAGANTIS_KEY_TEST', 'TPV_PAGANTIS_KEY_LIVE',
                                               'TPV_PAGANTIS_CURRENCY', 'TPV_PAGANTIS_ERROR_ACTION', 'TPV_PAGANTIS_ENV'));
                                               
    
    if (isset($config['TPV_PAGANTIS_URL']))
      $this->endpoint_url = $config['TPV_PAGANTIS_URL'];

    if (isset($config['TPV_PAGANTIS_ACCOUNT_ID_LIVE']))
      $this->account_id_live = $config['TPV_PAGANTIS_ACCOUNT_ID_LIVE'];

    if (isset($config['TPV_PAGANTIS_ACCOUNT_ID_TEST']))
      $this->account_id_test = $config['TPV_PAGANTIS_ACCOUNT_ID_TEST'];
            
    if (isset($config['TPV_PAGANTIS_KEY_LIVE']))
      $this->encryption_key_live = $config['TPV_PAGANTIS_KEY_LIVE']; 
      
    if (isset($config['TPV_PAGANTIS_KEY_TEST']))
      $this->encryption_key_test = $config['TPV_PAGANTIS_KEY_TEST']; 
            
    if (isset($config['TPV_PAGANTIS_CURRENCY']))
      $this->currency = $config['TPV_PAGANTIS_CURRENCY'];

    if (isset($config['TPV_PAGANTIS_ERROR_ACTION']))
      $this->error_action = $config['TPV_PAGANTIS_ERROR_ACTION'];
      
    if (isset($config['TPV_PAGANTIS_ENV']))
      $this->use_live_env = $config['TPV_PAGANTIS_ENV'];


    parent::__construct();

    $this->displayName = $this->l('Pagantis');
    $this->description = $this->l('Accept payments using pagantis.com');
    $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
    if (!count(Currency::checkPaymentCurrencies($this->id)))
      $this->warning = $this->l('No currency has been set for this module');
          
    if (!isset($this->account_id_live)
      || !isset($this->account_id_test)
      || !isset($this->encryption_key_live)
      || !isset($this->encryption_key_test)
      || !isset($this->currency) 
      || !isset($this->error_action)
      
    )
      $this->warning = $this->l('Your Pagantis module is not properly configured');
  }

  public function install()
  {
    if (!parent::install() 
    || !$this->registerHook('payment')
    || !$this->registerHook('paymentReturn')
    || !Configuration::updateValue('TPV_PAGANTIS_ACCOUNT_ID_LIVE', '')
    || !Configuration::updateValue('TPV_PAGANTIS_ACCOUNT_ID_TEST', '')
    || !Configuration::updateValue('TPV_PAGANTIS_KEY_LIVE', '') 
    || !Configuration::updateValue('TPV_PAGANTIS_KEY_TEST', '') 
    || !Configuration::updateValue('TPV_PAGANTIS_URL', 'https://psp.pagantis.com/2/sale')
    || !Configuration::updateValue('TPV_PAGANTIS_CURRENCY', 'EUR') 
    || !Configuration::updateValue('TPV_PAGANTIS_ERROR_ACTION', '0')
    || !Configuration::updateValue('TPV_PAGANTIS_ENV', '0'))
      return false;
    return true;
  }

  public function uninstall()
  {
    if (   !Configuration::deleteByName('TPV_PAGANTIS_URL')
        || !Configuration::deleteByName('TPV_PAGANTIS_ACCOUNT_ID_LIVE')
        || !Configuration::deleteByName('TPV_PAGANTIS_KEY_LIVE')
        || !Configuration::deleteByName('TPV_PAGANTIS_ACCOUNT_ID_TEST')
        || !Configuration::deleteByName('TPV_PAGANTIS_KEY_TEST')
        || !Configuration::deleteByName('TPV_PAGANTIS_CURRENCY') 
        || !Configuration::deleteByName('TPV_PAGANTIS_ERROR_ACTION')
        || !Configuration::deleteByName('TPV_PAGANTIS_ENV')
        || !parent::uninstall())
      return false;
    return true;
  }

  private function _postValidation()
  {
    if (Tools::isSubmit('btnSubmit'))
    {
      if (Tools::getValue('account_id_test') and substr(Tools::getValue('account_id_test'), 0 , 3) != 'tk_')
        $this->_postErrors[] = $this->l('Account_id should start by tk_ for test env');
      if (!Tools::getValue('account_id_test'))
        $this->_postErrors[] = $this->l('Account_id is mandatory for test env');
      if (!Tools::getValue('encryption_key_test'))
        $this->_postErrors[] = $this->l('Encription key for test env is mandatory');          
      if (Tools::getValue('pagantis_env') == 1)
      {
        if (Tools::getValue('account_id_live') and substr(Tools::getValue('account_id_live'), 0 , 3) != 'pk_')
          $this->_postErrors[] = $this->l('Account_id should start by pk_ for live env');
        if (!Tools::getValue('account_id_live'))
          $this->_postErrors[] = $this->l('Account_id is mandatory for live env');
        if (!Tools::getValue('encryption_key_live'))
          $this->_postErrors[] = $this->l('Encription key for live env is mandatory');
      }
      if (!Tools::getValue('currency'))
        $this->_postErrors[] = $this->l('You need to set a currency');
    }
  }

  private function _postProcess()
  {
    if (Tools::isSubmit('btnSubmit'))
    {
      Configuration::updateValue('TPV_PAGANTIS_ACCOUNT_ID_LIVE',  Tools::getValue('account_id_live'));
      Configuration::updateValue('TPV_PAGANTIS_ACCOUNT_ID_TEST',  Tools::getValue('account_id_test'));
      Configuration::updateValue('TPV_PAGANTIS_KEY_LIVE', Tools::getValue('encryption_key_live'));
      Configuration::updateValue('TPV_PAGANTIS_KEY_TEST', Tools::getValue('encryption_key_test'));
      Configuration::updateValue('TPV_PAGANTIS_CURRENCY',    Tools::getValue('currency'));
      Configuration::updateValue('TPV_PAGANTIS_ERROR_ACTION', Tools::getValue('error_action'));
      Configuration::updateValue('TPV_PAGANTIS_ENV', Tools::getValue('pagantis_env'));
    }
    $this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('ok').'" /> '.$this->l('Settings updated').'</div>';
  }


  private function _displayForm()
  {
    
    $this->_html .= '<img src="../modules/tpvpagantis/logo.gif" style="float:left; margin-right:15px;"><b>'.$this->l('This module allows you to accept payments by credit/debit card using Pagantis.com').'</b><br /><br />';
    
    $current_env=Tools::getValue('pagantis_env', $this->use_live_env);

    $this->_html .=

    '<form action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" method="post">

      <fieldset>
    
        <legend><img src="../img/admin/contact.gif" />'.$this->l('Configuration').'</legend>
        <p class="clear">'.$this->l('Please complete the information below. You can find all this informations in API section of https://bo.pagantis.com').'.</p><br />
        <label>'.$this->l('Choose environment').'</label>
        <div class="margin-form">
          <input type="radio" name="pagantis_env" id="env_test" value="0" '.(($current_env)==0 ? 'checked="checked" ' : '').'/>
          <label class="t" for="env_test">'.$this->l('Test').'</label>
          <input type="radio" name="pagantis_env" id="env_live" value="1" '.(($current_env)==1 ? 'checked="checked" ' : '').'/>
          <label class="t" for="env_live">'.$this->l('Live').'</label>
        </div>
        <br />
        <label>'.$this->l('Account ID for test environment').'</label>
          <div class="margin-form" style="height: 35px;"><input type="text" name="account_id_test" value="'.
          Tools::getValue('account_id_test', $this->account_id_test).
          '" style="width: 330px;" /></div>
        <label>'.$this->l('Encription key for test environment').'</label>
          <div class="margin-form" style="height: 35px;"><input type="text" name="encryption_key_test" value="'.
          Tools::getValue('encryption_key_test', $this->encryption_key_test).
          '" style="width: 330px;" /></div>
        <br />
        <label>'.$this->l('Account ID for live environment').'</label>
          <div class="margin-form" style="height: 35px;"><input type="text" name="account_id_live" value="'.
          Tools::getValue('account_id_live', $this->account_id_live).
          '" style="width: 330px;" /></div>
        <label>'.$this->l('Encription key for live environment').'</label>
          <div class="margin-form" style="height: 35px;"><input type="text" name="encryption_key_live" value="'.
          Tools::getValue('encryption_key_live', $this->encryption_key_live).
          '" style="width: 330px;" /></div>
        <br />
        <label>'.$this->l('Currency').'</label>
          <div class="margin-form"><input type="text" name="currency" value="'.
          Tools::getValue('currency', $this->currency).
          '" style="width: 330px;" /></div>
          
        <label>'.$this->l('Action on payment error').'</label>
        <div class="margin-form">
          <input type="radio" name="error_action" id="erroraction_nochange" value="0" checked="checked" />
          <label class="t" for="erroraction_nochange">'.$this->l('Show order again so customer can use a different payment method').'</label><br />
        </div>    
        <br><br>
        <input class="button" name="btnSubmit" value="'.$this->l('Update settings').'" type="submit" />
      </fieldset>
    </form>';
      
  }

  public function getContent()
  {
    $this->_html = '<h2>'.$this->l('Pagantis').'</h2>';

    if (Tools::isSubmit('btnSubmit'))
    {
      $this->_postValidation();
      if (!count($this->_postErrors))
        $this->_postProcess();
      else
        foreach ($this->_postErrors as $err)
          $this->_html .= '<div class="alert error">'.$err.'</div>';
    }
    else
      $this->_html .= '<br />';

    $this->_displayForm();

    return $this->_html;
  }


  
  public function generateSignature($signature_string){
    
    if(Tools::getValue('pagantis_env', $this->use_live_env)==1){
      $this->key_to_use= Tools::getValue('encryption_key_live', $this->encryption_key_live); 
    } else{
      $this->key_to_use= Tools::getValue('encryption_key_test', $this->encryption_key_test); 
    }
    
    return sha1($this->key_to_use.$signature_string);
  }
  
  public function execPayment($cart)
  {

    global $cookie;
    $customer = new Customer((int)($cart->id_customer)); //newline
      
    $cart_products = $cart->getProducts();
    
    $products_desc = '';
    foreach ($cart_products as $product) {
      $products_desc .= $product['quantity'].' '.$product['name'].'\n';
    }
    $products_desc = substr($products_desc, 0, 1000);
    
    $amount = number_format(Tools::convertPrice((($cart->getOrderTotal(true, 3))), $currency), 2, '.', '');
    $amount = str_replace('.','',$amount); 
        
    if(Tools::getValue('pagantis_env', $this->use_live_env)==0){
      $enc_key    = Tools::getValue('encryption_key_test', $this->encryption_key_test);
      $account_id = Tools::getValue('account_id_test', $this->account_id_test);
    }
    else{
      $enc_key    = Tools::getValue('encryption_key_live', $this->encryption_key_live);
      $account_id = Tools::getValue('account_id_live', $this->account_id_live);
    }
    $pagantis_endpoint = Tools::getValue('url', $this->endpoint_url);
    
    
    $currency        = Tools::getValue('currency', $this->currency);
    $order_id        = rand (0, 9) . rand (0, 9) . rand (0, 9) . rand (0, 9) . rand (0, 9) . $cart->id;
    
    // URL fix for v1.6
    // $url_OK  = 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'order-confirmation.php?key='.$customer->secure_key.'&id_cart='.$cart->id.'&id_module='.(int)($this->id).'&id_order='.(int)($cart->id);
    $current_server = Tools::getShopDomainSsl(true, true);
    $url_OK  = $current_server.__PS_BASE_URI__.'index.php?controller=order-confirmation&key='.$customer->secure_key.'&id_cart='.$cart->id.'&id_module='.(int)($this->id).'&id_order='.(int)($cart->id);
    $url_NOK = $current_server.__PS_BASE_URI__.'modules/tpvpagantis/payment_ko.php';    

    $cypher_method = "SHA1";
    
    $signature=$this->generateSignature($account_id.$order_id.$amount.$currency.$cypher_method.$url_OK.$url_NOK);
    
    return array(
      'pagantis_endpoint' => $pagantis_endpoint,
      'account_id'        => $account_id,
      'currency'          => $currency,
      'ok_url'            => $url_OK,
      'nok_url'           => $url_NOK,
      'order_id'          => $order_id,
      'amount'            => $amount,
      'description'       => $products_desc,
      'signature'         => $signature,
      'customer'          => ($cookie->logged ? $cookie->customer_firstname.' '.$cookie->customer_lastname : false)
    );
  }



  public function hookPayment($params)
  {
    if (!$this->active)
      return;
      
    if (!$this->checkCurrency($params['cart']))
      return;
    
    global $smarty;
    
    //$cart = $this->context->cart;
    $cart = $params['cart'];

    if (!$this->checkCurrency($cart))
      Tools::redirect('index.php?controller=order');
      $tpvpagantis = new TpvPagantis;
      $payment = $tpvpagantis->execPayment($cart);
      
    $this->smarty->assign(array(

      'nbProducts'        => $cart->nbProducts(),
      'cust_currency'     => $cart->id_currency,
      'currencies'        => $this->getCurrency((int)$cart->id_currency),
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
      'this_path'         => $this->getPathUri(),
      'this_path_ssl'     => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'

    ));
    
    return $this->display(__FILE__, 'payment.tpl');
  }

  
  public function hookPaymentReturn($params)
  {
    if (!$this->active)
      return ;
    global $smarty;
    return $this->display(__FILE__, 'views/templates/front/payment_ok.tpl');
  }
  
  public function checkCurrency($cart)
  {
    $currency_order = new Currency($cart->id_currency);
    $currencies_module = $this->getCurrency($cart->id_currency);

    if (is_array($currencies_module))
      foreach ($currencies_module as $currency_module)
        if ($currency_order->id == $currency_module['id_currency'])
          return true;
    return false;
  }

}
