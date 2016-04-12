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
      <th style="width: 50px;">{l s='Order Ref'}</th>
      <th style="width: 80px;">{l s='Parcel Number'}</th>
      <th style="width: 30px;">{l s='Weight'}</th>
      <th style="width: 30px;">{l s='Length'}</th>
      <th style="width: 30px;">{l s='Height'}</th>
      <th style="width: 30px;">{l s='Depth'}</th>
      <th style="width: 30px;">{l s='Value'}</th>
      <th style="width: 50px;">{l s='Location'}</th>
      <th style="width: 100px;">{l s='Recipient'}</th>
      <th style="width: 200px;">{l s='Address'}</th>
      <th style="width: 80px;">{l s='Services'}</th>
    </tr>      
  </thead>
  <tbody>
    {foreach $list as $key => $row}
    <tr>
      <td style="width: 20px;">{$key+1}</td>
      <td style="width: 50px;">{$row['reference']}</td>
      <td style="width: 80px;">{$row['parcel_number']}</td>
      <td style="width: 30px;">{$row['weight']}</td>
      <td style="width: 30px;">{$row['length']}</td>
      <td style="width: 30px;">{$row['height']}</td>
      <td style="width: 30px;">{$row['depth']}</td>
      <td style="width: 30px;">{$row['value']}</td>
      <td style="width: 50px;">{$row['id_location']}</td>
      <td style="width: 100px;">{$row['recipient']}</td>
      <td style="width: 200px;">{$row['address']}</td>
      <td style="width: 80px;">{$row['services']}</td>
    </tr>
    {/foreach}
  </tbody>
</table>

