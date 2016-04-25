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

class DpdCarrierDpdShopLocatorModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        parent::init();
        
        $this->module->loadHelper();

        $this->disLogin = DpdHelper::getLogin();
    }
    
    public function display()
    {
        $controller_path = $this->context->link->getModuleLink(
            'dpdcarrier',
            'dpdshoplocator',
            array('ajax' => 'true')
        );
        
        $this->context->smarty->assign(array(
            'controller_path' => $controller_path,
            'gmapsKey' => "AIzaSyAE_349qqoMOecarUr_IV6Gapq8lwZYaKY"
        ));
        
        $this->setTemplate('shop_locator.tpl');
        $this->display_header = false;
        $this->display_footer = false;
        parent::display();
    }
    
    public function displayAjax()
    {
        $this->output = array(
            'success' => array()
            ,'info' => array()
            ,'warning' => array()
            ,'error' => array()
            ,'validation' => array()
        );
        if (Tools::getIsset('action')) {
            switch(Tools::getValue('action')) {
                case 'find':
                    $this->findShops();
                    break;
                case 'info':
                    $this->getShopInfo();
                    break;
                case 'save':
                    $this->actionSaveShop();
                    break;
                default:
                    Tools::Redirect(__PS_BASE_URI__);
                    break;
            }
        }
        echo Tools::jsonEncode($this->output);
        die;
    }
    
    private function findShops()
    {
        $searchData = array();
        $deliveryAddress = new Address($this->context->cart->id_address_delivery);
        
        if (Tools::getIsset('lng') && Tools::getIsset('lat')) {
            $searchData = array(
                'Long' => Tools::getValue('lng')
                ,'Lat' => Tools::getValue('lat')
            );
        } elseif (Tools::getIsset('query') && Tools::getValue('query') != '') {
            $searchData = array(
                'Query' => Tools::getValue('query')
            );
        } else {
            $searchData = array(
                'Street' => $deliveryAddress->address1
                ,'HouseNo' => ''
                ,'Country' => Country::getIsoById($deliveryAddress->id_country)
                ,'ZipCode' => $deliveryAddress->postcode
                ,'City' => $deliveryAddress->city
            );
        }
        
        if (Tools::getIsset('day') && Tools::getValue('day') != '') {
            $searchData['DayOfWeek'] = Tools::getValue('day');
        }
        if (Tools::getIsset('time') && Tools::getValue('time') != '') {
            $searchData['TimeOfDay'] = Tools::getValue('time');
        }
        
        DpdHelper::loadDis();
        $shopFinder = new DisParcelShopFinder($this->disLogin);
        $result = $shopFinder->search($searchData);

        if ($result) {
            $this->output['success']['dpd-locator'] = count($result->shops) . ' ' . $this->module->l('shops found');
            $this->output['data']['center'] = $result->center;
            $counter = 0;
            foreach ($result->shops as $shopID => $shop) {
                $logo = '/modules/' . $this->module->name . '/lib/DIS/templates/img/icon_parcelshop.png';
                $select_link = $this->module->l('Select this parcelshop.');
                $active = true;
                if (Country::getIsoById($deliveryAddress->id_country) != $shop->isoAlpha2) {
                    $shopID = -1;
                    $logo = '/modules/' . $this->module->name . '/lib/DIS/templates/img/icon_parcelshop_na.png';
                    $select_link = $this->module->l('Please use a delivery address.');
                    $active = false;
                } else {
                    if($counter == 0) {
                        $this->_saveShop($shop);
                    }
                }
                
                $this->output['data']['shops'][] = array(
                    'id' => $shopID
                    ,'active' => $active
                    ,'lng' => $shop->longitude
                    ,'lat' => $shop->latitude
                    ,'name' => $shop->company
                    ,'address' => $shop->street . ' ' .
                        $shop->houseNo . ', ' .
                        $shop->zipCode . ' ' .
                        $shop->city
                    ,'logo' => array(
                        'url' => $logo
                        ,'size' => array(
                            'width' => 110
                            ,'height' => 120
                        )
                        ,'scaled' => array(
                            'width' => 55
                            ,'height' => 60
                        )
                        ,'anchor' => array(
                            'x' => 17,
                            'y' => 34
                        )
                        ,'origin' => array(
                            'x' => 0
                            ,'y' => 0
                        )
                    )
                    ,'infoLink' => $this->module->l('Show more information')
                    ,'selectLink' => $select_link
                );
                $counter++;
            }
            
            $cookie = new Cookie('dpdshops');
            $cookie->last_search = serialize($searchData);
            $cookie->write();
        } else {
            $this->output['warning']['dpd-locator'] = $this->module->l('No shops found');
        }
    }
    
    private function _saveShop($shop)
    {
        $id_cart = $this->context->cart->id;
        $id_carrier = $this->context->cart->id_carrier;
        $id_location = $shop->parcelShopId;
        $dbInstance = Db::getInstance();
        
        $dbInstance->insert(
            'dpdcarrier_pickup',
            array(
                'id_cart' => $dbInstance->escape($id_cart)
                ,'id_carrier' => $dbInstance->escape($id_carrier)
                ,'id_location' => $dbInstance->escape($id_location)
                ,'lat' => $dbInstance->escape($shop->latitude)
                ,'lng' => $dbInstance->escape($shop->longitude)
                ,'name' => $dbInstance->escape($shop->company)
                ,'address' => $dbInstance->escape($shop->street . ' ' .$shop->houseNo)
                ,'city' => $dbInstance->escape($shop->city)
                ,'postcode' => $dbInstance->escape($shop->zipCode)
                ,'iso_code' => $dbInstance->escape($shop->isoAlpha2)
            ),
            false,
            true,
            DB::REPLACE
        );
    }
    
    private function getShopInfo()
    {
        $shop = $this->getProposedShop();
        
        if ($shop) {
            $this->output['data'] = '<div><table>';
            
            foreach ($shop->openingHours as $day) {
                $this->output['data'] .=
                    '<tr><td>' . $day->weekday .
                    '</td><td>' . $day->openMorning .
                    '</td><td>' . $day->closeMorning .
                    '</td><td>' . $day->openAfternoon .
                    '</td><td>' . $day->closeAfternoon .
                    '</td></tr>';
            }
            
            $this->output['data'] .= '</table></div>';
        } else {
            $this->output['error']['unknown-shopid'] = $this->module->l(
                "The shopID provided wasn't proposed " .
                "or is disabled since your lookup"
            );
        }
    }
    
    private function actionSaveShop()
    {
        $shop = $this->getProposedShop();
        
        if ($shop) {
            $this->saveShop($shop);
            
            $this->output['data'] = '<p>' . $this->module->l('You have chosen') .
                ': <strong>' . $shop->company . '</strong>' .
                '<br>' . $this->module->l('Located at') . ': ' . $shop->street . ' ' . $shop->houseNo .
                ', ' . $shop->zipCode  . ' ' . $shop->city . '</p>' .
                '<a href="#" onclick="javascript:showLocator();return false;">' .
                $this->module->l('Click here to alter your choice') . '</a>';
        } else {
            $this->output['error']['unknown-shopid'] = $this->module->l(
                "The shopID provided wasn't proposed " .
                "or is disabled since your lookup"
            );
        }
        
        //TODO - delete cookie.
        
    }
    
    private function getProposedShop()
    {
        if (!Tools::getIsset('dpdshopid') || Tools::getValue('dpdshopid') =='') {
            $this->output['validation']['dpdshopid'] = $this->module->l('No parcelshop selection found.');
        }
        
        if (count($this->output['validation']) == 0) {
            $cookie = new Cookie('dpdshops');
            $searchData = unserialize($cookie->last_search);
            
            $shopFinder = new DisParcelShopFinder($this->disLogin);
            $result = $shopFinder->search($searchData);
            
            if (!$result || !isset($result->shops[Tools::getValue('dpdshopid')])) {
                Logger::addLog(
                    'Customer, ' . $this->context->customer->firstname . ' ' .
                    $this->context->customer->lastname . ' (' .
                    $this->context->customer->id . '), tried to use a shop ID that wasn\'t proposed to him (' .
                    Tools::getValue('dpdshopid') . ')',
                    2,
                    null,
                    null,
                    null,
                    true
                );
                return false;
            }
            
            return $result->shops[Tools::getValue('dpdshopid')];
        }
        
        return false;
    }
}
