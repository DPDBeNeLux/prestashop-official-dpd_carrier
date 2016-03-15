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

<script type="text/javascript">
{literal}
    function disableOpcPayment(){
        $("#opc_payment_methods a").click(function(){
            alert('Don\'t forget to select a ParcelShop');
            $('html, body').animate({
                scrollTop: $("#dpdLocatorContainer").offset().top
            }, 2000);
            return false;
        });
    }
    
    function enableOpcPayment(){
        $("#opc_payment_methods a").unbind('click');
    }
    
    var dpdLocator = new DPD.locator({
      controller: '{/literal}{$controller_path}{literal}',
      containerId: 'dpdLocatorContainer',
      fullscreen: false,
      daysOfTheWeek: ['All Week', 'M', 'T', 'W', 'T', 'F', 'S', 'S'],
      timeOfDay: ['All Day'],
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
    }
    
    $('#carrier_area').ready(function(){
        $('[id^="delivery_option_"]').each(function(index) {
            // if it is parcelshop option
            if(this.value == '{/literal}{$carrier_id}{literal},'){
                // If the parcelshop option is selected on load
                if(this.checked){
                    dpdLocator.showLocator();
                    disableOpcPayment();
                    }
                this.onchange = function(){
                    disableOpcPayment();
                    dpdLocator.showLocator();
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