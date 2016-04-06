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

class AdminDpdStatsController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function displayAjax()
    {
        $parcelshop_carrier_id = Configuration::get($this->module->generateVariableName('pickup id'));
        $classic_carrier_id = Configuration::get($this->module->generateVariableName('home id'));
        $home_carrier_id = Configuration::get($this->module->generateVariableName('home with predict id'));
        
        $parcelshop_carrier = new Carrier($parcelshop_carrier_id);
        $classic_carrier = new Carrier($classic_carrier_id);
        $home_carrier = new Carrier($home_carrier_id);
        
        $references = array();
        $references[] = $parcelshop_carrier->id_reference;
        $references[] = $classic_carrier->id_reference;
        $references[] = $home_carrier->id_reference;
        
        $query = 'SELECT 
                `psc`.`id_reference`
                ,(SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'dpdcarrier_label` WHERE `id_order` = `psoc`.`id_order`) as `label_count`
                ,COUNT(*) as `order_count`
                ,MIN(`psoc`.`date_add`) as `first_order`
                ,MAX(`psoc`.`date_add`) as `last_order`
            FROM 
                `' . _DB_PREFIX_ . 'order_carrier` as `psoc` 
            LEFT JOIN 
                `' . _DB_PREFIX_ . 'carrier` as `psc`   
            ON 
                `psoc`.`id_carrier` = `psc`.`id_carrier`
            WHERE 
                `psc`.`id_reference` IN (' . implode($references, ', ') . ')
            GROUP BY 
                `psc`.`id_reference`';
                
        $query_result = Db::getInstance()->executeS($query);
        
        $result = array();
        
        if (count($query_result) > 0) {
            foreach ($query_result as $row => $data) {
                $carrier = Carrier::getCarrierByReference($data['id_reference']);
                if ($carrier) {
                    $result[$carrier->name]['checkout'] = $data['order_count'];
                    $result[$carrier->name]['labels'] = $data['label_count'];
                }
            }
        }
        
        echo Tools::jsonEncode($result);
        die;
    }
    
}
