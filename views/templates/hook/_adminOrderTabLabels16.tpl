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
    var DPD = new function() {
        this.init = function() {
            loadLabelInfo();
        };
        
        var loadLabelInfo = function() {
            var xhr = new XMLHttpRequest();
            
            xhr.open("POST", "{$controllerUrl}", true);
            
            var params = "action=info&id_order={$order->id|escape:'htmlall':'UTF-8'}";
            
            //Send the proper header information along with the request
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.setRequestHeader("Content-length", params.length);
            xhr.setRequestHeader("Connection", "close");
            
            xhr.onload = function (e) {
              if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                      result = JSON.parse(xhr.responseText);
                      parseLabels(result);
                    } catch (e) {
                      console.error(xhr.responseText);
                      return;
                    }
                } else {
                  console.error(xhr.statusText);
                }
              }
            };
            xhr.onerror = function(e) {
              console.error(xhr.statusText);
            };
            xhr.send(params);
        };
        
        this.generateLabels = function() {
            hideCounter();
            showLoader();
            var form = document.getElementById("dpd_generate_label_form");
            var xhr = new XMLHttpRequest();
            
            var params = "";
            var sForm = serialize(form);
            for(var key in sForm) {
              params += "&" + key + "=" + sForm[key];
            }
            xhr.open("POST", "{$controllerUrl}", true);
            
            //Send the proper header information along with the request
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.setRequestHeader("Content-length", params.length);
            xhr.setRequestHeader("Connection", "close");

            xhr.onload = function (e) {
              if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                      result = JSON.parse(xhr.responseText);
                      parseLabels(result);
                    } catch (e) {
                      //displayError(xhr.responseText);
                      console.error(xhr.responseText);
                      return;
                    }
                } else {
                  console.error(xhr.statusText);
                }
              }
            };
            xhr.onerror = function (e) {
              console.error(xhr.statusText);
            };
            xhr.send(params);
        }
        
        this.updateLabelCount = function(e) {
            var inpValue = document.getElementById("dpd_label_value");
            
            inpValue.value = (inpValue.defaultValue / e.target.value);
        }
        
        this.optionSelected = function(e) {
            switch(e.target.name) {
                case "cod_delivery":
                    uncheck([
                        "dpd_e10_delivery"
                        ,"dpd_e12_delivery"
                        ,"dpd_e18_delivery"
                        ,"dpd_dps_delivery"
                        ,"dpd_sat_delivery"
                    ]);
                    break
                case "comp_delivery":
                    uncheck([
                        "dpd_dps_delivery"
                    ]);
                    break;
                case "e10_delivery":
                    uncheck([
                        "dpd_cod_delivery"
                        ,"dpd_e12_delivery"
                        ,"dpd_e18_delivery"
                        ,"dpd_dps_delivery"
                        ,"dpd_predict_delivery"
                        ,"dpd_sat_delivery"
                    ]);
                    break
                case "e12_delivery":
                    uncheck([
                        "dpd_cod_delivery"
                        ,"dpd_e10_delivery"
                        ,"dpd_e18_delivery"
                        ,"dpd_dps_delivery"
                        ,"dpd_predict_delivery"
                        ,"dpd_sat_delivery"
                    ]);
                    break
                case "e18_delivery":
                    uncheck([
                        "dpd_cod_delivery"
                        ,"dpd_e12_delivery"
                        ,"dpd_dps_delivery"
                        ,"dpd_predict_delivery"
                        ,"dpd_sat_delivery"
                    ]);
                    break
                case "dps_delivery":
                    uncheck([
                        "dpd_cod_delivery"
                        ,"dpd_comp_delivery"
                        ,"dpd_e10_delivery"
                        ,"dpd_e12_delivery"
                        ,"dpd_e18_delivery"
                        ,"dpd_predict_delivery"
                        ,"dpd_sat_delivery"
                    ]);
                    break;
                case "predict_delivery":
                    uncheck([
                        "dpd_e10_delivery"
                        ,"dpd_e12_delivery"
                        ,"dpd_e18_delivery"
                        ,"dpd_dps_delivery"
                    ]);
                    break;
                case "sat_delivery":
                    uncheck([
                        "dpd_cod_delivery"
                        ,"dpd_e10_delivery"
                        //,"dpd_e12_delivery"
                        ,"dpd_e18_delivery"
                        ,"dpd_dps_delivery"
                    ]);
                    break;
            }
        };
        
        this.togleOptions = function() {
            var options = document.getElementById("dpd_additional_options");
            
            if(options.style.display == "") {
                options.style.display = "none";
            } else {
                options.style.display = "";
            }
        }
        var uncheck = function (fields) {
            for(var i = 0; i < fields.length; i++) {
                document.getElementById(fields[i]).checked = false;
            }
        };
        
        var serialize = function (form) {
            if (!form || form.nodeName !== "FORM") {
                return;
            }
            var i, j,
                obj = {};
            for (i = form.elements.length - 1; i >= 0; i = i - 1) {
                if (form.elements[i].name === "") {
                    continue;
                }
                switch (form.elements[i].nodeName) {
                case 'INPUT':
                    switch (form.elements[i].type) {
                    case 'text':
                    case 'hidden':
                    case 'password':
                    case 'button':
                    case 'reset':
                    case 'submit':
                        obj[form.elements[i].name] = encodeURIComponent(form.elements[i].value);
                        break;
                    case 'checkbox':
                    case 'radio':
                        if (form.elements[i].checked) {
                            obj[form.elements[i].name] = encodeURIComponent(form.elements[i].value);
                        }
                        break;
                    case 'file':
                        break;
                    }
                    break;
                case 'TEXTAREA':
                    obj[form.elements[i].name] = encodeURIComponent(form.elements[i].value);
                    break;
                case 'SELECT':
                    switch (form.elements[i].type) {
                    case 'select-one':
                        obj[form.elements[i].name] = encodeURIComponent(form.elements[i].value);
                        break;
                    case 'select-multiple':
                        for (j = form.elements[i].options.length - 1; j >= 0; j = j - 1) {
                            if (form.elements[i].options[j].selected) {
                                obj[form.elements[i].name] = encodeURIComponent(form.elements[i].options[j].value);
                            }
                        }
                        break;
                    }
                    break;
                case 'BUTTON':
                    switch (form.elements[i].type) {
                    case 'reset':
                    case 'submit':
                    case 'button':
                        obj[form.elements[i].name] = encodeURIComponent(form.elements[i].value);
                        break;
                    }
                    break;
                }
            }
            return obj;
        };
        
        var showLoader = function() {
            var loader = document.getElementById("dpd_label_count_loading");
            loader.style.display = "inline-block";
        };
        
        var hideLoader = function() {
            var loader = document.getElementById("dpd_label_count_loading");
            loader.style.display = "none";
        };
        
        var showCounter = function(count) {
            var counter = document.getElementById("dpd_label_count");
            
            var startCount = parseInt(counter.innerHTML);
            
            if (typeof count != 'undefined') {
                counter.innerHTML = (startCount + count);
            }
            counter.style.display = "inline-block";
        };
        
        var hideCounter = function() {
            var counter = document.getElementById("dpd_label_count");
            counter.style.display = "none";
        };
        
        var insertAfter = function(newNode, referenceNode) {
            referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
        };
        
        var parseLabels = function(data) {
            hideLoader();
            showCounter(data.success.length);
            
            var noLabelRow = document.getElementById("dpd_no_label");
            
            if (data.success.length > 0) {
                noLabelRow.style.display = "none";
                document.getElementById("dpd_download_label").style.display = "";
            }
            
            for (var i = 0; i < data.success.length; i++) {
                var newTr = getNewLabelRow(data.success[i]);
                noLabelRow.parentNode.insertBefore(newTr, noLabelRow);
            }
            console.log(data.success);
        };
        
        var getNewLabelRow = function(data) {
            var newTr = document.createElement("tr");
            
            var tdCb = document.createElement("td");
            var cb = document.createElement("input");
            cb.type = "checkbox";
            cb.name = "selected_labels[]";
            cb.value = data.parcel_number;
            
            tdCb.appendChild(cb);
            newTr.appendChild(tdCb);
            
            var tdPn = document.createElement("td");
            var pnLink = document.createElement("a");
            pnLink.href = "{$controllerUrl}&action=download&selected_labels[]=" + data.parcel_number;
            pnLink.innerHTML = data.parcel_number;
            
            tdPn.appendChild(pnLink);
            newTr.appendChild(tdPn);
            
            var tdDate = document.createElement("td");
            tdDate.innerHTML = data.date;

            newTr.appendChild(tdDate);
            
            var tdWeight = document.createElement("td");
            tdWeight.innerHTML = data.weight;
            
            newTr.appendChild(tdWeight);
            
            var tdDim = document.createElement("td");
            tdDim.innerHTML = data.length + " x " + data.depth + " x " + data.height;
            
            newTr.appendChild(tdDim);
            
            var tdValue = document.createElement("td");
            tdValue.innerHTML = data.value;
            
            newTr.appendChild(tdValue);
            
            var tdInfo = document.createElement("td");
            
            var info = "";
            for (var service in data.services) {
                // skip loop if the property is from prototype
                if(!data.services.hasOwnProperty(service)) continue;

                if(data.services[service] == true) {
                    info += " " + service;
                    if(service == "dps") {
                        info += "(" + data.id_location + ")";
                    }
                }
            }
            
            tdInfo.innerHTML = info;
            
            newTr.appendChild(tdInfo);
            
            return newTr;
        };
    };
    window.addEventListener("load", DPD.init, false);
</script>
<style>
    .dpd-arrow-down {
        width: 0; 
        height: 0; 
        border-left: 10px solid transparent;
        border-right: 10px solid transparent;

        border-top: 10px solid #000;
    }
</style>
<div class="tab-pane" id="labels">
    <form action="{$controllerUrl}" method="post" class="form-horizontal well hidden-print">
        <div class="table-responsive">
            <table class="table" id="labels_table">
                <thead>
                    <tr>
                        <th>
                            <span class="title_box "></span>
                        </th>
                        <th>
                            <span class="title_box ">{l s='Number' mod='dpdcarrier'}</span>
                        </th>
                        <th>
                            <span class="title_box ">{l s='Date' mod='dpdcarrier'}</span>
                        </th>
                        <th>
                            <span class="title_box ">{l s='Weight' mod='dpdcarrier'}</span>
                        </th>
                        <th>
                            <span class="title_box ">{l s='Dimensions' mod='dpdcarrier'}</span>
                        </th>
                        <th>
                            <span class="title_box ">{l s='Value' mod='dpdcarrier'}</span>
                        </th>
                        <th>
                            <span class="title_box ">{l s='Extra Info' mod='dpdcarrier'}</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr id="dpd_no_label">
                        <td colspan="7" class="list-empty">
                            <div class="list-empty-msg">
                                <i class="icon-warning-sign list-empty-icon"></i>
                                {l s='There is no label available' mod='dpdcarrier'}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td id="dpd_download_label" colspan="7" class="list-empty" style="display: none;">
                            <button type="submit" name="action" class="btn btn-primary" value="download">
                                {l s='Download Label(s)' mod='dpdcarrier'}
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </form>
    <form action="javascript:DPD.generateLabels();" method="post" id="dpd_generate_label_form" class="form-horizontal well hidden-print">
        <div class="row">
            <div class="form-group pull-left col-lg-5">
                <label class="control-label col-lg-4">{l s='Count' mod='dpdcarrier'}</label>
                <input type="text" name="label_count" class="form-control fixed-width-sm" value="1" onchange="DPD.updateLabelCount(event);"/>
                <input type="hidden" name="id_order" value="{$order->id|escape:'htmlall':'UTF-8'}" />
            </div>
            <button type="submit" name="action" class="btn btn-primary pull-right" value="generate">
                {l s='Generate Label(s)' mod='dpdcarrier'}
            </button>
        </div>
        <div class="table-responsive">
            <table class="table" id="labels_table">
                <thead>
                    <tr>
                        <th colspan=7>
                            <a class="title_box " onclick="DPD.togleOptions()">Additional Options</a>
                        </th>
                    </tr>
                </thead>
                <tbody id="dpd_additional_options" style="display: none;">
                    <tr>
                        <td>
                            <label class="control-label col-lg-3">{l s='Weight' mod='dpdcarrier'}</label>
                            <input type="text" name="label_weight" class="form-control fixed-width-sm" value="{$order_weight|escape:'htmlall':'UTF-8'}"/>
                            <label class="control-label col-lg-3">{l s='Length' mod='dpdcarrier'}</label>
                            <input type="text" name="label_length" class="form-control fixed-width-sm" value="0" />
                            <label class="control-label col-lg-3">{l s='Height' mod='dpdcarrier'}</label>
                            <input type="text" name="label_height" class="form-control fixed-width-sm" value="0" />
                            <label class="control-label col-lg-3">{l s='Depth' mod='dpdcarrier'}</label>
                            <input type="text" name="label_depth" class="form-control fixed-width-sm" value="0" />
                            <label class="control-label col-lg-3">{l s='Value' mod='dpdcarrier'}</label>
                            <input type="text" id="dpd_label_value" name="label_value" class="form-control fixed-width-sm" value="{$order->total_paid|escape:'htmlall':'UTF-8'}" />
                            <label class="control-label col-lg-3">{l s='Shop ID' mod='dpdcarrier'}</label>
                            <input type="text" id="dpd_label_ps_id" name="label_ps_id" class="form-control fixed-width-sm" value="{if isset($shop_info['id_location'])}{$shop_info['id_location']|escape:'htmlall':'UTF-8'}{/if}"/>
                        </td>
                        <td>
                            <label class="control-label col-lg-3 nowrap">{l s='COD' mod='dpdcarrier'}</label>
                            <input type="checkbox" id="dpd_cod_delivery" name="cod_delivery" value="1" class="form-control fixed-width-sm" onchange="DPD.optionSelected(event);" {if $init_settings['cod']}checked{/if}/>
                            <label class="control-label col-lg-3 nowrap">{l s='Complete' mod='dpdcarrier'}</label>
                            <input type="checkbox" id="dpd_comp_delivery" name="comp_delivery" value="1" class="form-control fixed-width-sm" onchange="DPD.optionSelected(event);" {if $init_settings['comp']}checked{/if}/>
                            <label class="control-label col-lg-3 nowrap">{l s='Express 10' mod='dpdcarrier'}</label>
                            <input type="checkbox" id="dpd_e10_delivery" name="e10_delivery" value="1" class="form-control fixed-width-sm" onchange="DPD.optionSelected(event);" {if $init_settings['e10']}checked{/if}/>
                            <label class="control-label col-lg-3 nowrap">{l s='Express 12' mod='dpdcarrier'}</label>
                            <input type="checkbox" id="dpd_e12_delivery" name="e12_delivery" value="1" class="form-control fixed-width-sm" onchange="DPD.optionSelected(event);" {if $init_settings['e12']}checked{/if}/>
                        </td>
                        <td>
                            <label class="control-label col-lg-3 nowrap">{l s='Guarantee 18' mod='dpdcarrier'}</label>
                            <input type="checkbox" id="dpd_e18_delivery" name="e18_delivery" value="1" class="form-control fixed-width-sm" onchange="DPD.optionSelected(event);" {if $init_settings['e18']}checked{/if}/>
                            <label class="control-label col-lg-3 nowrap">{l s='Pickup' mod='dpdcarrier'}</label>
                            <input type="checkbox" id="dpd_dps_delivery" name="dps_delivery" value="1" class="form-control fixed-width-sm" onchange="DPD.optionSelected(event);" {if $init_settings['dps']}checked{/if}>
                            <label class="control-label col-lg-3 nowrap">{l s='Predict' mod='dpdcarrier'}</label>
                            <input type="checkbox" id="dpd_predict_delivery" name="predict_delivery" value="1" class="form-control fixed-width-sm" onchange="DPD.optionSelected(event);" {if $init_settings['predict']}checked{/if}/>
                            <label class="control-label col-lg-3 nowrap">{l s='Saturday' mod='dpdcarrier'}</label>
                            <input type="checkbox" id="dpd_sat_delivery" name="sat_delivery" value="1" class="form-control fixed-width-sm" onchange="DPD.optionSelected(event);"/ {if $init_settings['sat']}checked{/if}>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </form>
</div>
