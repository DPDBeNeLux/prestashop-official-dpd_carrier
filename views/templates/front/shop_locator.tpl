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
<html>
  <head>
    <script src="/modules/dpdcarrier/lib/DIS/js/dpdParcelshopLocator.js"></script>
    <script>
      {literal}
      var dpdLocator;
      function init() {
        dpdLocator = new DPD.locator({
          controller: '{/literal}{$controller_path}{literal}',
          containerId: 'dpdLocatorContainer',
          fullscreen: false,
          daysOfTheWeek: [{/literal}
            '{l s='All Week' mod='dpdcarrier'}',
            '{l s='Mo' mod='dpdcarrier'}',
            '{l s='Tu' mod='dpdcarrier'}',
            '{l s='We' mod='dpdcarrier'}',
            '{l s='Th' mod='dpdcarrier'}',
            '{l s='Fr' mod='dpdcarrier'}',
            '{l s='Sa' mod='dpdcarrier'}',
            '{l s='Su' mod='dpdcarrier'}'{literal}
          ],
          loadingMessage: {/literal}'{l s='Loading dpd Pickup points' mod='dpdcarrier'}'{literal},
          notFoundMessage: {/literal}'{l s='No Pickup points found' mod='dpdcarrier'}'{literal},
          timeOfDay: ['{/literal}{l s='All Day' mod='dpdcarrier'}{literal}'],
          callBack: chosenShop
        });
        
        dpdLocator.autoShowLocator = true;
        
        var fileref = document.createElement('script');
        fileref.setAttribute("type","text/javascript");
        fileref.setAttribute("src", 'https://maps.googleapis.com/maps/api/js?key={/literal}{$gmapsKey}{literal}&libraries=places&callback=dpdLocator.initialize');
        document.getElementsByTagName("head")[0].appendChild(fileref);
      }
      
      function showLocator() {
          dpdLocator.showLocator();
          parent.DpdAdjustIframeHeight(document.getElementById("dpdLocatorContainer").scrollHeight);
      }
      
      function chosenShop(data) {
          dpdLocator.hideLocator();
          parent.DpdAdjustIframeHeight(document.getElementById("dpdLocatorContainer").scrollHeight);
      }
      {/literal}
    </script>
    <link rel="stylesheet" href="/modules/dpdcarrier/lib/DIS/templates/css/locator.css" type="text/css" media="all" />
    <style>
      body {
        margin: 0;
        padding: 0;
      }
      #dpdLocatorContainer {
        top: 0;
        left: 0;
      }
    </style>
  </head>
  <body onload="init();">
  <div id="dpdLocatorContainer">
  </div>
  </body>
</html>