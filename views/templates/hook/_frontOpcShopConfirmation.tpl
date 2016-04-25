{literal}
<div class="box dpd_selected_parcelshop">
    <p>You have chosen to pickup your parcel in a DPD Pickup point:</p>
    <h1>{/literal}{$shop_info['name']|escape:'htmlall':'UTF-8'}{literal}</h1>
    <p>{/literal}{$shop_info['address']|escape:'htmlall':'UTF-8'}<br>{$shop_info['postcode']|escape:'htmlall':'UTF-8'} {$shop_info['city']|escape:'htmlall':'UTF-8'}{literal}</p>
</div>
{/literal}