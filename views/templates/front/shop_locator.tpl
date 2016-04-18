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
    </style>
  </head>
  <body onload="init();">
  <div id="dpdLocatorContainer">
  </div>
  </body>
</html>