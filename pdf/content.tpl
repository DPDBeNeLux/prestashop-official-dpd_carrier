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
<table style="width: 80%; font-size:16px;">
  <thead>
    <tr>
      <th style="width: 20px;"></th>
      <th style="width: 50px;">{l s='Order Ref' mod='dpdcarrier'}</th>
      <th style="width: 80px;">{l s='Parcel Number' mod='dpdcarrier'}</th>
      <th style="width: 30px;">{l s='Weight' mod='dpdcarrier'}</th>
      <th style="width: 30px;">{l s='Length' mod='dpdcarrier'}</th>
      <th style="width: 30px;">{l s='Height' mod='dpdcarrier'}</th>
      <th style="width: 30px;">{l s='Depth' mod='dpdcarrier'}</th>
      <th style="width: 30px;">{l s='Value' mod='dpdcarrier'}</th>
      <th style="width: 50px;">{l s='Location' mod='dpdcarrier'}</th>
      <th style="width: 100px;">{l s='Recipient' mod='dpdcarrier'}</th>
      <th style="width: 200px;">{l s='Address' mod='dpdcarrier'}</th>
      <th style="width: 80px;">{l s='Services' mod='dpdcarrier'}</th>
    </tr>      
  </thead>
  <tbody>
    {foreach $list as $key => $row}
    <tr>
      <td style="width: 20px;">{$key+1}</td>
      <td style="width: 50px;">{$row['reference']|escape:'htmlall':'UTF-8'}</td>
      <td style="width: 80px;">{$row['parcel_number']|escape:'htmlall':'UTF-8'}</td>
      <td style="width: 30px;">{$row['weight']|escape:'htmlall':'UTF-8'}</td>
      <td style="width: 30px;">{$row['length']|escape:'htmlall':'UTF-8'}</td>
      <td style="width: 30px;">{$row['height']|escape:'htmlall':'UTF-8'}</td>
      <td style="width: 30px;">{$row['depth']|escape:'htmlall':'UTF-8'}</td>
      <td style="width: 30px;">{$row['value']|escape:'htmlall':'UTF-8'}</td>
      <td style="width: 50px;">{$row['id_location']|escape:'htmlall':'UTF-8'}</td>
      <td style="width: 100px;">{$row['recipient']|escape:'htmlall':'UTF-8'}</td>
      <td style="width: 200px;">{$row['address']|escape:'htmlall':'UTF-8'}</td>
      <td style="width: 80px;">{$row['services']|escape:'htmlall':'UTF-8'}</td>
    </tr>
    {/foreach}
  </tbody>
</table>

