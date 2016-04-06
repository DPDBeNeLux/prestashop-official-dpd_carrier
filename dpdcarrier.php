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

class DpdCarrier extends CarrierModule
{
    public $download_location;
    
    private $neededControllers = array(
        'AdminDpdStats' => 'DPD Stats'
        ,'AdminDpdConfig' => 'DPD Configuration'
    );
    
    private $hooks = array(
        'actionCarrierUpdate' // Triggered when carrier is edited in back-end
        ,'displayBeforeCarrier' // Used to display the map before the carrier selection
        ,'actionCarrierProcess'
        ,'displayPayment'
        ,'displayBeforePayment'
        ,'displayOrderConfirmation'
        ,'displayAdminOrderTabOrder'
        ,'displayAdminOrderContentOrder'
        ,'actionOrderStatusUpdate'
    );
    
    public function __construct()
    {
        $this->download_location = _PS_DOWNLOAD_DIR_ . 'dpd';
        
        $this->loadDis();
        
        $this->version = '0.2.0';
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
        $this->dependencies = array();
        $this->name = 'dpdcarrier';
        $this->displayName = $this->l('DPD Carrier 2.0');
        $this->description = $this->l('Description Small');
        $this->author = 'Michiel Van Gucht';
        $this->author_uri = 'https://be.linkedin.com/in/mvgucht';
        $this->description_full = $this->l('Description Full');
        $this->additional_description = $this->l('Additional Description');
        // This loads the module every time the back-end is loaded so we can check some stuff.
        $this->need_instance = 1;
        $this->tab = 'shipping_logistics';
        $this->warning; // Fill this variable with warnings for the shipper (that is why we need need_instance)
        $this->limited_countries = array('be', 'lu', 'nl'); // Just to be a douche :)
        // $this->controllers = array('DpdStats', 'DpdConfig');  // This doesn't work.
        
        $this->bootstrap = true; // can't remember why. TODO: check this.
        
        parent::__construct();

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the DPD Carrier Module?');
        
        // This will check the module when it is called due to need_instance.
        if (self::isInstalled($this->name)) {
            //$this->checkConfiguraion();
        }
        
        //This is a value required by prestashop to show that the module is healthy.
        //Configuration::updateValue('MYMODULE_CONFIGURATION_OK', true);
    }
    
    /**
     *
     */
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
          
        if (!parent::install()
          || !$this->initCarriers()
          || !$this->installTab()
          || !$this->installDB()) {
            return false;
        }
        
        foreach ($this->hooks as $hook_name) {
            if (!$this->registerHook($hook_name)) {
                return false;
            }
        }
        
        if (count($this->installControllers($this->neededControllers)) > 0) {
            //ADD ERROR STUFF HERE
            return false;
        }
        
        // TODO: CREATE DOWNLOAD LOCATION.
        
        return true;
    }
    
    public function uninstall()
    {
        
        if (!parent::uninstall()){
            $this->warning[] = "Could not run parent uninstaller successfully.";
        }
        
        if (!$this->removeCarriers()) {
            $this->warning[] = "Could not remove carriers";
        }

        foreach ($this->hooks as $hook_name) {
            if (!$this->unregisterHook($hook_name)) {
                $this->warning[] = "Could not unhook hook " . $hook_name;
            }
        }
        
        if (count($this->uninstallControllers($this->neededControllers)) > 0) {
            // ADD ERROR STUFF HERE
        }
        
        return true;
    }
    
    /**
     *
     */
    private function installControllers($list)
    {
        $failed = array();
        foreach ($list as $name => $userReadableName) {
            $tab = new Tab();
            $tab->active = 1;
            $tab->class_name = $name;
            // Hide the tab from the menu.
            $tab->id_parent = -1;
            $tab->module = $this->name;
            
            $tab->name = array();
            foreach (Language::getLanguages(true) as $lang) {
                $tab->name[$lang['id_lang']] = $userReadableName;
            }
            if (!$tab->add()) {
                $failed[$name] = $userReadableName;
            }
        }

        return $failed;
    }
    
    private function installTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->name = array();
        $tab->class_name = 'AdminDpdLabels';

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'DPD Shipping List';
        }

        $tab->id_parent = (int)Tab::getIdFromClassName('AdminShipping');
        $tab->module = $this->name;

        return $tab->add();
    }
    
    private function installDB()
    {
        $file = dirname(__FILE__) .'/install/install.sql';
        $handle = fopen($file, "r");
        $query = fread($handle, filesize($file));
        fclose($handle);
        
        $query = preg_replace('/_PREFIX_/', _DB_PREFIX_, $query);
        
        return Db::getInstance()->execute($query);
    }
    
    private function uninstallControllers($list)
    {
        $failed = array();
        foreach ($list as $name => $userReadableName) {
            $id_tab = (int)Tab::getIdFromClassName($name);

            if ($id_tab) {
                $tab = new Tab($id_tab);
                if (!$tab->delete()) {
                    $failed[$name] = $userReadableName;
                }
            }
        }

        return $failed;
    }
    
    private function initCarriers()
    {
        $this->loadDis();
        
        $weight_multiplier = $this->getWeightMultiplier();
        $dimension_multiplier = $this->getDimensionMultiplier();
        $languages = Language::getLanguages(true);
        $shipping_services = new DisServices();
        $default = $shipping_services->default;
        
        // Return false if no services defined.
        if (!isset($shipping_services->services)) {
            return false;
        }
        
        $country_iso =  $this->context->country->iso_code;
        $language_iso = $this->context->language->iso_code;
        
        foreach ($shipping_services->services as $service) {
            $max_width = (isset($service->max_width) ? $service->max_width : $default->max_width);
            $max_height = (isset($service->max_height) ? $service->max_height : $default->max_height);
            $max_depth = (isset($service->max_depth) ? $service->max_depth : $default->max_depth);
            $max_weight = (isset($service->max_weight) ? $service->max_weight : $default->max_weight);
        
            $carrier = new Carrier();
            $carrier->name = $service->name;
            $carrier->url = 'https://tracking.dpd.de/parcelstatus?locale=' . $language_iso . '_' . 
                $country_iso .'&query=@';
            $carrier->active = true;
            $carrier->shipping_handling = true;
            $carrier->range_behavior = 0;
            $carrier->shipping_external = false;
            $carrier->external_module_name = $this->name;
            $carrier->need_range = false;
            $carrier->max_width = $max_width * $dimension_multiplier;
            $carrier->max_height = $max_height * $dimension_multiplier;
            $carrier->max_depth = $max_depth * $dimension_multiplier;
            $carrier->max_weight = $max_weight * $weight_multiplier;
            $carrier->grade = 9;

            foreach ($languages as $language) {
                $carrier->delay[$language['id_lang']] = $service->description; //TODO: ADD TRANSLATION
            }
            
            // Save the new carrier
            if (!$carrier->add()) {
                return false;
            } else {
                $groups = Group::getGroups(true);
                foreach ($groups as $group) {
                    Db::getInstance()->insert('carrier_group', array(
                        'id_carrier' => (int) $carrier->id,
                        'id_group' => (int) $group['id_group']
                    ), false, true, Db::ON_DUPLICATE_KEY);
                }
                
                $weight_ranges = (isset($service->weight_ranges) ? $service->weight_ranges : $default->weight_ranges);
                $ranges = array();
                
                for ($i = 0; $i < count($weight_ranges) - 1; $i++) {
                    $rangeWeight = new RangeWeight();
                    $rangeWeight->id_carrier = $carrier->id;
                    $rangeWeight->delimiter1 = $weight_ranges[$i] * $weight_multiplier;
                    $rangeWeight->delimiter2 = $weight_ranges[$i + 1] * $weight_multiplier;
                    $rangeWeight->add();
                    
                    $ranges[] = $rangeWeight;
                }
                
                $zones = (isset($service->zones) ? $service->zones : $default->zones);
                
                foreach ($zones as $zone_name) {
                    $zone = new Zone(Zone::getIdByName($zone_name));
                    Db::getInstance()->insert(
                        'carrier_zone',
                        array(
                            'id_carrier' => (int)$carrier->id,
                            'id_zone' => (int)$zone->id),
                        false,
                        true,
                        Db::ON_DUPLICATE_KEY
                    );

                    foreach ($ranges as $range) {
                        Db::getInstance()->insert(
                            'delivery'
                            , array(
                                'id_carrier' => $carrier->id
                                ,'id_range_price' => null
                                ,'id_range_weight' => (int)$range->id
                                ,'id_zone' => (int)$zone->id
                                ,'price' => '0'
                            )
                            , true
                            , true
                            , Db::ON_DUPLICATE_KEY
                        );
                    }
                }
            }
            copy(
                dirname(__FILE__) . '/lib/DIS/img/' . Tools::strtolower(str_replace(' ', '_', $service->name)) . '.jpg'
                , _PS_SHIP_IMG_DIR_ . '/' . (int)$carrier->id . '.jpg'
            );
              
            Configuration::updateValue($this->generateVariableName($service->name . ' id'), (int)($carrier->id));
        }
        return true;
    }
    
    private function removeCarriers()
    {
        $this->loadDis();
        $shipping_services = new DisServices();
        
        foreach ($shipping_services->services as $service) {
            $carrier_var_name = $this->generateVariableName($service->name . ' id');
            $carrier = new Carrier(Configuration::get($carrier_var_name));
            
            if (!$carrier->delete() || !Configuration::deleteByName($carrier_var_name)) {
                $this->warning[] = "Could not delete carrier " . $service->name;
            }
        }
        
        return count($this->warning) == 0;
    }
    /**
     *  The configuration screen content.
     */
    public function getContent()
    {
        $this->context->controller->addCSS($this->_path.'lib/DIS/templates/css/main.css');
        $this->context->controller->addJS($this->_path.'lib/DIS/js/dpdAdminConfig.js');
        
        $dpdTemplate = dirname(__FILE__) . '/lib/DIS/templates/dpdAdminConfig.html';
        $handle = fopen($dpdTemplate, 'r');
        $content = fread($handle, filesize($dpdTemplate));
        fclose($handle);
        
        $this->context->smarty->assign(
            array(
                'configControllerUrl' => $this->context->link->getAdminLink('AdminDpdConfig') . "&ajax=true"
                ,'statsControllerUrl' => $this->context->link->getAdminLink('AdminDpdStats') . "&ajax=true"
            )
        );

        return $this->display(__FILE__, '_dpdAdminConfigValues.tpl') . $content;
    }
    
    public function hookActionCarrierUpdate($params)
    {
        $dis_services = new DisServices();
        
        foreach ($dis_services->services as $service) {
            $var_name = $this->generateVariableName($service->name . ' id');
            
            if ((int)($params['id_carrier']) == (int)(Configuration::get($var_name))) {
                Configuration::updateValue($var_name, (int)($params['carrier']->id));
            }
        }
    }
    
    public function hookDisplayBeforeCarrier()
    {
        $this->context->controller->addCSS($this->_path.'lib/DIS/templates/css/locator.css');
        $this->context->controller->addJS($this->_path.'lib/DIS/js/dpdParcelshopLocator.js');
        
        $controller_path = $this->context->link->getModuleLink(
            'dpdcarrier'
            , 'dpdshoplocator'
            , array('ajax' => 'true')
        );
        
        $this->context->smarty->assign(
            array(
                'controller_path' => $controller_path
                ,'carrier_id' => Configuration::get('DPDCARRIER_PICKUP_ID')
            )
        );
        
        return $this->display(__FILE__, '_dpdLocator.tpl');
    }
    
    public function hookActionCarrierProcess($params)
    {
        if ((int)($params['cart']->id_carrier) == (int)(Configuration::get('DPDCARRIER_PICKUP_ID'))) {
            if (!$this->getParcelShopInfo($params['cart'])) {
                $this->context->controller->errors[] = Tools::displayError('Please select a parcelshop before proceeding.');
            }
        }
    }
    
    public function hookDisplayOrderConfirmation($params)
    {
        $cart = new Cart($params['objOrder']->id_cart);
        $this->context->smarty->assign(
            array(
                'shop_info' => $this->getParcelShopInfo($cart)
            )
        );
        
        return $this->display(dirname(__FILE__), '_frontOrderConfirmation.tpl');
    }
    
    public function hookDisplayAdminOrderTabOrder($params)
    {
        if ($this->isDpdOrder($params['order'])) {
            return $this->display(__FILE__, '_adminOrderTab.tpl');
        }
    }
    
    public function hookDisplayAdminOrderContentOrder($params)
    {
        if ($this->isDpdOrder($params['order'])) {
            $order_carrier = new OrderCarrier($params['order']->getIdOrderCarrier());
            $cart = new Cart($params['order']->id_cart);
            $this->context->smarty->assign(
                array(
                    'controllerUrl' => $this->context->link->getAdminLink('AdminDpdLabels') . "&ajax=true"
                    ,'order_weight' => $order_carrier->weight
                    ,'order' => $params['order']
                    ,'shop_info' => $this->getParcelShopInfo($cart)
                    ,'init_settings' => $this->getInitialOrderSettings($params['order'])
                )
            );
            
            return $this->display(dirname(__FILE__), '_adminOrderTabLabels16.tpl');
        }
    }
    
    public function hookActionOrderStatusUpdate($params)
    {
        if($params['newOrderStatus']->id == (int)Configuration::get($this->generateVariableName('label on status'))) {
            $labelController = AdminController::getController('AdminDpdLabelsController');
            $labelController->generateDefaultLabel($params['id_order']);
        }
    }
    
    public static function isDpdOrder($order)
    {
        $current_carrier = new Carrier($order->id_carrier);
        
        return $current_carrier->external_module_name == 'dpdcarrier';
    }
    
    private function isCOD($order) {
        return $order->module == 'cashondelivery';
    }
    
    private function getInitialOrderSettings($order) {
        $currenct_carrier = new Carrier($order->id_carrier);
        
        $this->loadDis();
        
        $result = array();
        
        $shipping_services = new DisServices();
        foreach ($shipping_services->services as $service) {
        
            $service_carrier_id = Configuration::get($this->generateVariableName($service->name . ' id'));
            if($service_carrier_id) {
                $service_carrier = new Carrier($service_carrier_id);
                if($currenct_carrier->id_reference == $service_carrier->id_reference) {
                    $result = $service->shipment_settings;
                    break;
                }
            }
        }
        if(count($result) == 0) {
            $result = $shipping_services->default->shipment_settings;
        }
        $result['cod'] = $this->isCOD($order);
        
        return $result;
    }
    
    /**
     * Mandatory functions for a CarrierModule.
     */
    public function getOrderShippingCost($params, $shipping_cost)
    {
        
    }
    
    public function getOrderShippingCostExternal($params)
    {
      
    }
    
    public function loadDis()
    {
        $files = preg_grep('/index\.php$/', glob($this->local_path . '/lib/DIS/classes/*.php'), PREG_GREP_INVERT);
        
        foreach ($files as $filename) {
            require_once($filename);
        }
    }
    
    public function getLogin()
    {
        $this->loadDis();
        
        $delisId = Configuration::get('DPD_DIS_delisid');
        $delisPw = Configuration::get('DPD_DIS_password');

        $url = Configuration::get('DPD_DIS_live_server') == 1 ?
            'https://public-dis.dpd.nl/Services/' :
            'https://public-dis-stage.dpd.nl/Services/';
        
        return new DisLogin($delisId, $delisPw, $url);
    }
    
    public function generateVariableName($input)
    {
        return Tools::strtoupper($this->name . '_' . str_replace(" ", "_", $input));
    }
    
    public function getWeightMultiplier()
    {
        $weight_multiplier = 1;
        switch(configuration::get('PS_WEIGHT_UNIT')) {
            case 'mg':
                $weight_multiplier = 1000000;
                break;
            case 'g':
                $weight_multiplier = 1000;
                break;
            case 'Kg':
                $weight_multiplier = 1;
                break;
            case 'lbs':
                $weight_multiplier = 0.45359237;
                break;
            case 'st':
                $weight_multiplier = 6.35029318;
                break;
            default:
                $weight_multiplier = 1;
                break;
        }
        return $weight_multiplier;
    }
    
    public function getDimensionMultiplier()
    {
        $dimension_multiplier = 1;
        switch(configuration::get('PS_DIMENSION_UNIT')) {
            case 'mm':
                $dimension_multiplier = 10;
                break;
            case 'cm':
                $dimension_multiplier = 1;
                break;
            case 'dm':
                $dimension_multiplier = 0.1;
                break;
            case 'm':
                $dimension_multiplier = 0.01;
                break;
            case 'in':
                $dimension_multiplier = 2.54;
                break;
            case 'ft':
                $dimension_multiplier = 30.48;
                break;
            default:
                $dimension_multiplier = 1;
                break;
        }
        return $dimension_multiplier;
    }
    
    public function getParcelShopInfo($cart)
    {
        $query = new DbQuery();
        $query->select('*')->from('dpdcarrier_pickup')->where('id_cart = ' . $cart->id);
        
        return Db::getInstance()->getRow($query);
    }
}