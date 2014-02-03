{capture name=path}{l s='Secure payment with credit/debit card' mod='tpvpagantis'}{/capture}

{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Order summary' mod='tpvpagantis'}</h2>

{assign var='current_step' value='payment'}

{if $nbProducts <= 0}
  <p class="warning">{l s='Your shopping cart is empty.'}</p>
{else}

<h3>{l s='Secure payment with credit/debit card' mod='tpvpagantis'}</h3>

<div style="float: right">
  <img src="{$this_path}/img/cc_brands.png" alt="{l s='credit card logos' mod='tpvpagantis'}" style=" margin: 0px 10px 5px 0px;" />
</div>

<form action="{$pagantis_endpoint}" method="post" id="tpvpagantis_form"> 

  <p>
    <b>{l s='Here is a short summary of your order:' mod='tpvpagantis'}</b>
  </p>

  <p>
    - {l s='The total amount for your order is' mod='tpvpagantis'}
      <span id="amount" class="price">{displayPrice price=$total}</span>
  </p>

  <p style="margin-top: 4em;">
    <strong>{l s='Please confirm your order by clicking "Make payment"' mod='tpvpagantis'}</strong>
  </p>
  <p>
    {l s="You-ll be redirected to our payment gateway where you will have to enter your credit card details to finalize the order." mod='tpvpagantis'}
  </p>

<p class="cart_navigation">
  <a href="{$base_dir_ssl}order.php?step=3" class="button_large">{l s='Change payment method' mod='tpvpagantis'}</a>
  <input type="hidden" name="account_id"  value="{$account_id}" /> 
  <input type="hidden" name="currency"    value="{$currency}" />
  <input type="hidden" name="ok_url"      value="{$ok_url}" />
  <input type="hidden" name="nok_url"     value="{$nok_url}" />
  <input type="hidden" name="order_id"    value="{$order_id}" />
  <input type="hidden" name="amount"      value="{$amount}" />
  <input type="hidden" name="description" value="{$description}" />
  <input type="hidden" name="signature"   value="{$signature}" />
  <input type="submit" name="submit"      value="{l s='Make payment' mod='tpvpagantis'}" class="exclusive_large" />
</p>

</form>

{/if}