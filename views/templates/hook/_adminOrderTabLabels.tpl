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
{if $version == '1.6'}
<div class="dpd tab-pane" id="labels">
{else}
<div class="clear" style="margin-top: 10px;"></div>
<fieldset class="dpd">
	<legend>
      <img src="../img/admin/delivery.gif" />DPD <span class="dpd loading" id="dpd_label_count_loading"></span><span class="badge" id="dpd_label_count">0</span>
  </legend>
{/if}
    <form action="{html_entity_decode($controllerUrl|escape:'htmlall':'UTF-8')}" method="post" class="form-horizontal well hidden-print">
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
            <div class="form-group pull-left" id="generate_count">
                <label class="control-label">{l s='Count' mod='dpdcarrier'}</label>
                <input type="text" name="label_count" class="form-control fixed-width-sm" value="1" onchange="DPD.updateLabelCount(event);"/>
                <input type="hidden" name="id_order" value="{$order->id|escape:'htmlall':'UTF-8'}" />
            </div>
            <button id="generate_label" type="submit" name="action" class="btn btn-primary pull-right" value="generate">
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
                            <label class="control-label">{l s='Weight' mod='dpdcarrier'}</label>
                            <input type="text" name="label_weight" class="form-control" value="{$order_weight|escape:'htmlall':'UTF-8'}"/></br>
                            <label class="control-label">{l s='Length' mod='dpdcarrier'}</label>
                            <input type="text" name="label_length" class="form-control" value="0" /></br>
                            <label class="control-label">{l s='Height' mod='dpdcarrier'}</label>
                            <input type="text" name="label_height" class="form-control" value="0" /></br>
                            <label class="control-label">{l s='Depth' mod='dpdcarrier'}</label>
                            <input type="text" name="label_depth" class="form-control" value="0" /></br>
                            <label class="control-label">{l s='Value' mod='dpdcarrier'}</label>
                            <input type="text" id="dpd_label_value" name="label_value" class="form-control" value="{$order->total_paid|escape:'htmlall':'UTF-8'}" /></br>
                            <label class="control-label">{l s='Shop ID' mod='dpdcarrier'}</label>
                            <input type="text" id="dpd_label_ps_id" name="label_ps_id" class="form-control" value="{if isset($shop_info['id_location'])}{$shop_info['id_location']|escape:'htmlall':'UTF-8'}{/if}"/>
                        </td>
                        <td>
                            <label class="control-label nowrap">{l s='COD' mod='dpdcarrier'}</label>
                            <input type="checkbox" id="dpd_cod_delivery" name="cod_delivery" value="1" class="form-control" onchange="DPD.optionSelected(event);" {if $init_settings['cod']}checked{/if}/>
                            <label class="control-label nowrap">{l s='Complete' mod='dpdcarrier'}</label>
                            <input type="checkbox" id="dpd_comp_delivery" name="comp_delivery" value="1" class="form-control" onchange="DPD.optionSelected(event);" {if $init_settings['comp']}checked{/if}/>
                            <label class="control-label nowrap">{l s='Express 10' mod='dpdcarrier'}</label>
                            <input type="checkbox" id="dpd_e10_delivery" name="e10_delivery" value="1" class="form-control" onchange="DPD.optionSelected(event);" {if $init_settings['e10']}checked{/if}/>
                            <label class="control-label nowrap">{l s='Express 12' mod='dpdcarrier'}</label>
                            <input type="checkbox" id="dpd_e12_delivery" name="e12_delivery" value="1" class="form-control" onchange="DPD.optionSelected(event);" {if $init_settings['e12']}checked{/if}/>
                        </td>
                        <td>
                            <label class="control-label nowrap">{l s='Guarantee 18' mod='dpdcarrier'}</label>
                            <input type="checkbox" id="dpd_e18_delivery" name="e18_delivery" value="1" class="form-control" onchange="DPD.optionSelected(event);" {if $init_settings['e18']}checked{/if}/>
                            <label class="control-label nowrap">{l s='Pickup' mod='dpdcarrier'}</label>
                            <input type="checkbox" id="dpd_dps_delivery" name="dps_delivery" value="1" class="form-control" onchange="DPD.optionSelected(event);" {if $init_settings['dps']}checked{/if}>
                            <label class="control-label nowrap">{l s='Predict' mod='dpdcarrier'}</label>
                            <input type="checkbox" id="dpd_predict_delivery" name="predict_delivery" value="1" class="form-control" onchange="DPD.optionSelected(event);" {if $init_settings['predict']}checked{/if}/>
                            <label class="control-label nowrap">{l s='Saturday' mod='dpdcarrier'}</label>
                            <input type="checkbox" id="dpd_sat_delivery" name="sat_delivery" value="1" class="form-control" onchange="DPD.optionSelected(event);"/ {if $init_settings['sat']}checked{/if}>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </form>
{if $version == '1.6'}
</div>
{else}
</fieldset>
{/if}
