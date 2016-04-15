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
<!-- Carrier DpdCarrier  -->
<div id="dpdLocatorContainer">
    <iframe id="dpdIframe" src="module/dpdcarrier/dpdshoplocator" style="width:100%; height:0px;"> </iframe>
</div>
<script>
{literal}
    function AdjustIframeHeight(i) { document.getElementById("dpdIframe").style.height = parseInt(i) + "px"; }
    
    function showLocator() {
        AdjustIframeHeight(600);
    }
    
    function hideLocator() {
        AdjustIframeHeight(0);
    }
    
    $('#carrier_area').ready(function(){
        $('[id^="delivery_option_"]').each(function(index) {
            // if it is parcelshop option
            if(this.value == '{/literal}{$carrier_id|escape:'htmlall':'UTF-8'}{literal},'){
                // If the parcelshop option is selected on load
                if(this.checked){
                    showLocator();
                }
                this.onchange = function(){
                    if (this.checked) {
                        showLocator();
                    }
                    return false;
                }
            } else {
                this.onchange = function(){
                    if (this.checked) {
                        hideLocator();
                    }
                    return false
                }
            }
        });
    });
    
{/literal}
</script>
<!-- End Carrier DpdCarrier  -->