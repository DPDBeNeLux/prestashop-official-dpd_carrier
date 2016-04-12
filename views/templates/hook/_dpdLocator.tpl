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
<style>
  .dpd-locator-controls {
    margin-top: 10px;
    border: 1px solid transparent;
    border-radius: 2px 0 0 2px;
    box-sizing: border-box;
    -moz-box-sizing: border-box;
    height: 32px;
    outline: none;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
  }

  #dpd-locator-input {
    background-color: #fff;
    font-family: Roboto;
    font-size: 15px;
    font-weight: 300;
    margin-left: 12px;
    padding: 0 11px 0 13px;
    text-overflow: ellipsis;
    width: 300px;
  }
  
  #dpd-locator-time,
  #dpd-locator-day {
    width: 80px;
  }

  #dpd-locator-input:focus {
    border-color: #4d90fe;
  }

  .pac-container {
    font-family: Roboto;
  }

  #type-selector {
    color: #fff;
    background-color: #4d90fe;
    padding: 5px 11px 0px 11px;
  }

  #type-selector label {
    font-family: Roboto;
    font-size: 13px;
    font-weight: 300;
  }
  #target {
    width: 345px;
  }
</style>
<!-- Carrier DpdCarrier  -->
<div id="dpdLocatorContainer"></div>

<script>
{literal}
    function disableOpcPayment(){
      $("#opc_payment_methods a").each(function(e) {
        $(this).mousedown( function(){
          alert("{/literal}{l s='Don\'t forget to select a ParcelShop' mod='dpdcarrier'}{literal}");
          $('html, body').animate({
            scrollTop: $("#dpdLocatorContainer").offset().top
          }, 2000);
          return false;
        });
      });
    }
    
    function enableOpcPayment(){
        $("#opc_payment_methods a").each(function(e) {
          $(this).unbind('mousedown');
        });
    }
    
    var dpdLocator = new DPD.locator({
      controller: '{/literal}{$controller_path}{literal}',
      containerId: 'dpdLocatorContainer',
      fullscreen: false,
      daysOfTheWeek: [
        {/literal}
        '{l s='All Week' mod='dpdcarrier'}',
        '{l s='Mo' mod='dpdcarrier'}',
        '{l s='Tu' mod='dpdcarrier'}',
        '{l s='We' mod='dpdcarrier'}',
        '{l s='Th' mod='dpdcarrier'}',
        '{l s='Fr' mod='dpdcarrier'}',
        '{l s='Sa' mod='dpdcarrier'}',
        '{l s='Su' mod='dpdcarrier'}'
        {literal}],
      timeOfDay: ['{/literal}{l s='All Day' mod='dpdcarrier'}{literal}'],
      callBack: chosenShop
    });
    
    if(typeof google == 'undefined' || typeof google.maps == 'undefined') {
        var fileref = document.createElement('script');
        fileref.setAttribute("type","text/javascript");
        fileref.setAttribute("src", 'https://maps.googleapis.com/maps/api/js?key=AIzaSyAE_349qqoMOecarUr_IV6Gapq8lwZYaKY&libraries=places&callback=dpdLocator.initialize');
        document.getElementsByTagName("head")[0].appendChild(fileref);
    } else {
        dpdLocator.initialize();
    }
    
    function chosenShop(shopID) {
        dpdLocator.hideLocator();
        enableOpcPayment();
    }
    
    $('#carrier_area').ready(function(){
        $('[id^="delivery_option_"]').each(function(index) {
            // if it is parcelshop option
            if(this.value == '{/literal}{$carrier_id|escape:'htmlall':'UTF-8'}{literal},'){
                // If the parcelshop option is selected on load
                if(this.checked){
                    dpdLocator.showLocator();
                    disableOpcPayment();
                }
                this.onchange = function(){
                    if (this.checked) {
                        disableOpcPayment();
                        dpdLocator.showLocator();
                    }
                    return false;
                }
            } else {
                this.onchange = function(){
                    enableOpcPayment();
                    dpdLocator.hideLocator();
                    return false
                }
            }
        });
    });
    
{/literal}
</script>
<!-- End Carrier DpdCarrier  -->