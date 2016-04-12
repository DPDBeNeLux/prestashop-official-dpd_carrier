<?php
/**
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
 */

class CashOnDeliveryOverride extends CashOnDelivery
{
    public function hasProductDownload($cart)
    {
        return parent::hasProductDownload($cart) || !$this->isCODAllowed($cart);
    }
    
    private function isCODAllowed($cart)
    {
        $id_carrier = $cart->id_carrier;
        $cart_carrier = new Carrier($id_carrier);
        
        $special_carrier_ids = array(
            Configuration::get('DPDCARRIER_PICKUP_ID')
            ,Configuration::get('DPDCARRIER_GUARANTEE_ID')
            ,Configuration::get('DPDCARRIER_EXPRESS_10_ID')
            ,Configuration::get('DPDCARRIER_EXPRESS_10_ID')
            ,Configuration::get('DPDCARRIER_EXPRESS_12_ID')
        );
        
        foreach ($special_carrier_ids as $special_carrier_id) {
            $special_carrier = new Carrier($special_carrier_id);
        
            if ($special_carrier->id_reference == $cart_carrier->id_reference) {
                return false;
            }
        }
        
        return true;
        
    }
}
