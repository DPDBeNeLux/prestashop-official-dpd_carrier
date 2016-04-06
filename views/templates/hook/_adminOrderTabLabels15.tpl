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
<!-- DPD block -->
<div class="clear" style="margin-top: 10px;"></div>
<fieldset>
	<legend><img src="../img/admin/delivery.gif" /> {l s='Labels' mod='dpdcarrier'}</legend>
	<form action="{$downloadLink}" method="post" class="form-horizontal well hidden-print">
		<div class="clear" style="margin-bottom: 10px;"></div>
		<table class="table" width="100%" cellspacing="0" cellpadding="0" id="labels_table">
			<colgroup>
				<col width="10%"/>
				<col width=""/>
				<col width="30%"/>
				<col width="15%"/>
			</colgroup>
			<thead>
				<tr>
					<th />
					<th>{l s='Number' mod='dpdcarrier'}</th>
					<th>{l s='Date' mod='dpdcarrier'}</th>
					<th>{l s='Weight' mod='dpdcarrier'}</th>
				</tr>
			</thead>
			<tbody>
			{foreach from=$labels item=label}
				<tr id="label_{$label->number}">
					<td><input type="checkbox" name="selected_label[]" value="{$label->number}" /></td>
					<td><a href="{$downloadLink}&labelnumber={$label->number}">{$label->number}</a></td>
					<td>{$label->date_add}</td>
					<td>{$label->weight}</td>
				</tr>
			{foreachelse}
				<tr>
					<td colspan="4" class="list-empty">
						<div class="list-empty-msg">
							<i class="icon-warning-sign list-empty-icon"></i>
							{l s='There is no label available' mod='dpdcarrier'}
						</div>
					</td>
				</tr>
			{/foreach}
			{if $labels|@count}
				<tr>
					<td colspan="4" class="list-empty">
						<button type="submit" name="download_label" class="btn btn-primary" value="submit">
							{l s='Download Label(s)' mod='dpdcarrier'}
						</button>
					</td>
				</tr>
			{/if}
			</tbody>
		</table>
	</form>
	{if $labels|@count}
	<form action="{$downloadLink}" method="post" class="form-horizontal well hidden-print">
		<div class="row">
			<div class="col-lg-1">
				<input type="text" name="label_count" value="1" />
				<input type="hidden" name="id_order" value="{$id_order}" />
			</div>
			<div class="col-lg-3">
				<button type="submit" name="generate_label" class="btn btn-primary" value="submit">
					{l s='Generate Label(s)' mod='dpdcarrier'}
				</button>
			</div>
		</div>
	</form>
	{/if}
</fieldset>
<br />

