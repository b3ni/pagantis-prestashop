<div class="row">
  <div class="col-xs-12 col-md-6">
    <p class="payment_module">
      <a href="javascript:$('#pagantis_form').submit();" title="{l s='Pay by credit-debit card' mod='tpvpagantis'}" class="bankwire">
        {l s='Pay by credit-debit card' mod='tpvpagantis'}
        <br/>
        <img src="{$this_path}/img/cc_brands.png" title="{l s='Pay by credit-debit card' mod='tpvpagantis'}" alt="{l s='Pay by credit-debit card' mod='tpvpagantis'}"  />
      </a>    
    </p>

    <form action="{$pagantis_endpoint}" method="post" id="pagantis_form"> 
      <input type="hidden" name="account_id"  value="{$account_id}" /> 
      <input type="hidden" name="currency"    value="{$currency}" />
      <input type="hidden" name="ok_url"      value="{$ok_url}" />
      <input type="hidden" name="nok_url"     value="{$nok_url}" />
      <input type="hidden" name="order_id"    value="{$order_id}" />
      <input type="hidden" name="amount"      value="{$amount}" />
      <input type="hidden" name="description" value="{$description}" />
      <input type="hidden" name="signature"   value="{$signature}" />
    </form>
  </div>
</div>
