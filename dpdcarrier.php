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
    private $neededControllers = array(
        'AdminDpdStats' => 'DPD Stats'
        ,'AdminDpdConfig' => 'DPD Configuration'
    );
    
    private $hooks = array(
        'actionCarrierUpdate' // Triggered when carrier is edited in back-end
        ,'displayBeforeCarrier' // Used to display the map before the carrier selection
    );
    
    public function __construct()
    {
        $this->loadDis();
        
        $this->version = '0.2.0';
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
        $this->dependencies = array();
        $this->name = 'dpdcarrier';
        $this->displayName = $this->l('DPD Carrier 2.0');
        $this->description = $this->l('Start shipping with DPD today. A parcel 2home, 2shop or between your own stores? No problem!');
        $this->author = 'Michiel Van Gucht';
        $this->author_uri = 'https://be.linkedin.com/in/mvgucht';
        $this->description_full = $this->l('Description Full');
        $this->additional_description = $this->l('Additional Description');
        $this->need_instance = 1; // This loads the module every time the back-end is loaded so we can check some stuff.
        $this->tab = 'shipping_logistics';
        // $this->warning; // Fill this variable with warnings for the shipper (that is why we need need_instance)
        $this->limited_countries = array('be', 'lu', 'nl'); // Just to be a douche :)
        
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
          || !$this->installDB()) {
            return false;
        }
        
        foreach($this->hooks as $hook_name) {
            if(!$this->registerHook($hook_name)) {
                return false;
            }
        }
        
        if (count($this->installControllers($this->neededControllers)) > 0) {
            // ADD ERROR STUFF HERE
            return false;
        }
        return true;
    }
    
    public function uninstall()
    {
        if (!parent::uninstall()
          || $this->removeCarriers()) {
            return false;
        }
        
        foreach($this->hooks as $hook_name) {
            if(!$this->unregisterHook($hook_name)) {
                return false;
            }
        }
        
        if (count($this->uninstallControllers($this->neededControllers)) > 0) {
            // ADD ERROR STUFF HERE
            return false;
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
        if(!isset($shipping_services->services)) {
            return false;
        }
        
        foreach($shipping_services->services as $service) {
            $carrier = new Carrier();
            $carrier->name = $service->name;
            $carrier->url = 'https://tracking.dpd.de/parcelstatus?locale=' . $this->context->language->iso_code . '_' . $this->context->country->iso_code .'&query=@';
            $carrier->active = true;
            $carrier->shipping_handling = true;
            $carrier->range_behavior = 0;
            $carrier->shipping_external = false;
            $carrier->external_module_name = $this->name;
            $carrier->need_range = false;
            $carrier->max_width = (isset($service->max_width) ? $service->max_width : $default->max_width) * $dimension_multiplier;
            $carrier->max_height = (isset($service->max_width) ? $service->max_width : $default->max_width) * $dimension_multiplier;
            $carrier->max_depth = (isset($service->max_width) ? $service->max_width : $default->max_width) * $dimension_multiplier;
            $carrier->max_weight = (isset($service->max_weight) ? $service->max_weight : $default->max_weight) * $weight_multiplier;
            $carrier->grade = 9;

            foreach ($languages as $language) 
                $carrier->delay[$language['id_lang']] = $service->description; //TODO: ADD TRANSLATION
            
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

                for($i = 0; $i < count($weight_ranges) - 1; $i++)
                {
                  $rangeWeight = new RangeWeight();
                  $rangeWeight->id_carrier = $carrier->id;
                  $rangeWeight->delimiter1 = $weight_ranges[$i] * $weight_multiplier;
                  $rangeWeight->delimiter2 = $weight_ranges[$i + 1] * $weight_multiplier;
                  $rangeWeight->add();
                  
                  $ranges[] = $rangeWeight;
                }
                
                $zones = (isset($method->zones) ? $method->zones : $default->zones);
                
                foreach($zones as $zone_name)
                {
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

                    foreach($ranges as $range)
                    {
                        Db::getInstance()->insert(
                            'delivery', 
                            array(
                                'id_carrier' => $carrier->id, 
                                'id_range_price' => NULL, 
                                'id_range_weight' => (int)$range->id, 
                                'id_zone' => (int)$zone->id, 
                                'price' => '0'),
                            true, 
                            true, 
                            Db::ON_DUPLICATE_KEY
                        );
                    }
                }
            }
            copy(dirname(__FILE__) . '/lib/DIS/img/' . strtolower(str_replace(' ', '_', $service->name)) . '.jpg', _PS_SHIP_IMG_DIR_ . '/' . (int)$carrier->id . '.jpg');
              
            Configuration::updateValue($this->generateVariableName($service->name . ' id'), (int)($carrier->id));
        }
        return true;
    }
    
    private function removeCarriers()
    {
        $this->loadDis();
        $shipping_services = new DisServices();

        foreach($shipping_services->services as $service)
        {
            $carrier_var_name = $this->generateVariableName($service->name . ' id');
            $carrier = new Carrier(Configuration::get($carrier_var_name));
            
            if (!$carrier->delete() || !Configuration::deleteByName($carrier_var_name))
              return false;
        }
        
        return true;
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
    
    public function hookDisplayBeforeCarrier()
    {
        $this->context->controller->addCSS($this->_path.'lib/DIS/templates/css/locator.css');
        $this->context->controller->addJS($this->_path.'lib/DIS/js/dpdParcelshopLocator.js');
        
        $this->context->smarty->assign(
            array(
                'controller_path' => $this->context->link->getModuleLink('dpdcarrier','dpdshoplocator', array('ajax' => 'true'))
                ,'carrier_id' => Configuration::get('DPDCARRIER_PICKUP_ID')
            )
        );
        
        return $this->display(__FILE__, '_dpdLocator.tpl');
    }
    
    public function hookActionCarrierUpdate($params)
    {
      $dis_services = new DisServices();
      
      foreach ($dis_services->services as $service)
      {
        $var_name = $this->generateVariableName($service->name . ' id');
        
        if ((int)($params['id_carrier']) == (int)(Configuration::get($var_name)))
          Configuration::updateValue($var_name, (int)($params['carrier']->id));
      }
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
        foreach (preg_grep('/index\.php$/', glob($this->local_path . '/lib/DIS/classes/*.php'), PREG_GREP_INVERT) as $filename) {
            require_once($filename);
        }
    }
    
    private function generateVariableName($input)
    {
        return strtoupper($this->name . '_' . str_replace(" ", "_", $input));
    }
    
    private function getWeightMultiplier()
    {
      $weight_multiplier = 1;
      switch(_PS_WEIGHT_UNIT_) {
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
    
    private function getDimensionMultiplier() 
    {
        $dimension_multiplier = 1;
        switch(_PS_DIMENSION_UNIT_) {
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
}
