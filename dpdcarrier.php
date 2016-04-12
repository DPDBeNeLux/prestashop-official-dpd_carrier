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
        ,'actionCarrierProcess'
        ,'displayPayment'
        ,'displayBeforePayment'
        ,'displayOrderConfirmation'
        ,'displayAdminOrderTabOrder'
        ,'displayAdminOrderContentOrder'
        ,'actionOrderStatusUpdate'
        ,'displayPDFOrderReturn'
    );
    
    public function loadHelper()
    {
        require_once($this->local_path . DS .'helper.php');
    }
    
    public function __construct()
    {
        $this->loadHelper();
        DpdHelper::loadDis();
        
        $this->version = '0.2.0';
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
        $this->dependencies = array();
        $this->name = 'dpdcarrier' //DpdHelper::MODULENAME;
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
          || !DpdHelper::initCarriers()
          || !DpdHelper::installTab()
          || !DpdHelper::installDB()) {
            return false;
        }
        
        foreach ($this->hooks as $hook_name) {
            if (!$this->registerHook($hook_name)) {
                return false;
            }
        }
        
        if (count(DpdHelper::installControllers($this->neededControllers)) > 0) {
            //ADD ERROR STUFF HERE
            return false;
        }
        
        // TODO: CREATE DOWNLOAD LOCATION.
        
        return true;
    }
    
    public function uninstall()
    {
        
        if (!parent::uninstall()) {
            $this->warning[] = "Could not run parent uninstaller successfully.";
        }
        
        if (!DpdHelper::removeCarriers()) {
            $this->warning[] = "Could not remove carriers";
        }
        
        if (!DpdHelper::installTab()) {
            $this->warning[] = "Could not remove tab";
        }

        foreach ($this->hooks as $hook_name) {
            if (!$this->unregisterHook($hook_name)) {
                $this->warning[] = "Could not unhook hook " . $hook_name;
            }
        }
        
        if (count(DpdHelper::uninstallControllers($this->neededControllers)) > 0) {
            // ADD ERROR STUFF HERE
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
    
    public function hookActionCarrierUpdate($params)
    {
        $dis_services = new DisServices();
        
        foreach ($dis_services->services as $service) {
            $var_name = DpdHelper::generateVariableName($service->name . ' id');
            
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
            'dpdcarrier',
            'dpdshoplocator',
            array('ajax' => 'true')
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
        $currentPickupId = (int)(Configuration::get(DpdHelper::generateVariableName('PICKUP_ID')));
        if ((int)($params['cart']->id_carrier) == $currentPickupId) {
            if (!DpdHelper::getParcelShopInfo($params['cart'])) {
                $this->context->controller->errors[] = Tools::displayError('Please select a parcelshop before proceeding.');
            }
        }
    }
    
    public function hookDisplayOrderConfirmation($params)
    {
        $currentPickupId = (int)(Configuration::get(DpdHelper::generateVariableName('PICKUP_ID')));
        if ((int)($params['cart']->id_carrier) == $currentPickupId) {
            $cart = new Cart($params['objOrder']->id_cart);
            $this->context->smarty->assign(
                array(
                    'shop_info' => DpdHelper::getParcelShopInfo($cart)
                )
            );
            
            return $this->display(dirname(__FILE__), '_frontOrderConfirmation.tpl');
        }
    }
    
    public function hookDisplayAdminOrderTabOrder($params)
    {
        if (DpdHelper::isDpdOrder($params['order'])) {
            return $this->display(__FILE__, '_adminOrderTab.tpl');
        }
    }
    
    public function hookDisplayAdminOrderContentOrder($params)
    {
        if (DpdHelper::isDpdOrder($params['order'])) {
            $order_carrier = new OrderCarrier($params['order']->getIdOrderCarrier());
            $cart = new Cart($params['order']->id_cart);
            $this->context->smarty->assign(
                array(
                    'controllerUrl' => $this->context->link->getAdminLink('AdminDpdLabels') . "&ajax=true"
                    ,'order_weight' => $order_carrier->weight
                    ,'order' => $params['order']
                    ,'shop_info' => DpdHelper::getParcelShopInfo($cart)
                    ,'init_settings' => DpdHelper::getInitialOrderSettings($params['order'])
                )
            );
            
            return $this->display(dirname(__FILE__), '_adminOrderTabLabels16.tpl');
        }
    }
    
    public function hookActionOrderStatusUpdate($params)
    {
        $labelStatus = (int)Configuration::get(DpdHelper::generateVariableName('label on status');
        if ($params['newOrderStatus']->id == $labelStatus)) {
            $order = new Order($params['id_order']);
            $labels = DpdHelper::getOrderLabelInfo($order);
            if (count($labels) == 0) {
                DpdHelper::generateDefaultLabel($order);
            }
        }
    }
    
    public function hookDisplayPDFOrderReturn($params)
    {
        $result = DpdHelper::generateReturnLabel($params['object']);
        
        if ($result) {
            $label_image = DpdHelper::getLabelLocation() . DS . $result[0]['parcel_number'] .'.jpg';
        } else {
            $label_image = false;
        }
        
        $this->context->smarty->assign(
            array(
                'label_path' => $label_image
            )
        );
            
            return $this->display(dirname(__FILE__), '_frontPDFOrderReturn.tpl');
        
        
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
}