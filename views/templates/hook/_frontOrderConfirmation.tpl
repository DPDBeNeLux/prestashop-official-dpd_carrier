{*
* 2014-2016 DPD
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Michiel Van Gucht <michiel.vangucht@dpd.be>
*  @copyright 2014-2016 Michiel Van Gucht
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*
*
*                     N dyyh N
*                   dhyyyyyyyyhd
*              N hyyyyyyyyyyyyyyyyhdN
*          N dyyyyyyyyyyyyyyyyyyyyyyyyd N
*         hyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyh
*         N dyyyyyyyyyyyyyyyyyyyyyyyyyyh
*       d     Ndhyyyyyyyyyyyyyyyyyyyd      dN
*       yyyh N   N dyyyyyyyyyyyyhdN   N hyyyN
*       yyyyyyhd     NdhyyyyyyyN   NdhyyyyyyN
*       yyyyyyyyyyh N   N hyyyyhddyyyyyyyyyyN
*       yyyyyyyyyyyyyhd     yyyyyyyyyyyyyyyyN
*       yyyyyyyyyyyyyyyyd   yyyyyyyyyyyyyyyyN
*       yhhhyyyyyyyyyyyyd   yyyyyyyyyyyyyyyyN
*       hhhhhyyyyyyyyyyyd   yyyyyyyyyyyyyyyyN
*       hhhhhhhyyyyyyyyyd   yyyyyyyyyyyyyyyyN
*       hhhhhhhhyyyyyyyyd   yyyyyyyyyyyyyyyyN
*       N dhhhhhhhyyyyyyd   yyyyyyyyyyyyyh N
*           Ndhhhhhyyyyyd   yyyyyyyyyyd
*              N hhhhyyyh NdyyyyyyhdN
*                 N dhhyyyyyyyyh N
*                     Ndhyyhd N
*                        NN
*}
<script>
    {literal}
    var selector;
    if($('#center_column .box').length == 0){
      selector = $('#center_column h1')
    } else {
      selector = $('#center_column .box')
    }
    
    $(document).ready(function(){
        $('<div class="box dpd_selected_parcelshop"> \
              <p>{/literal}{l s='You have chosen to pickup your parcel in a DPD Pickup point' mod='dpdcarrier'}{literal}:</p> \
              <h2>{/literal}{$shop_info['name']|escape:'htmlall':'UTF-8'}{literal}</h2> \
              <p>{/literal}{$shop_info['address']|escape:'htmlall':'UTF-8'}<br>{$shop_info['postcode']|escape:'htmlall':'UTF-8'} {$shop_info['city']|escape:'htmlall':'UTF-8'}{literal}</p> \
          </div>').insertAfter(selector);
    });
        
    {/literal}
</script>

