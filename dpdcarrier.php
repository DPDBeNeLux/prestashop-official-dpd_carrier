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
        require_once(dirname(__FILE__) . DS .'helper.php');
    }
    
    private $warnings = array();
    
    public function __construct()
    {
        $fullDescription = $this->l('Integrate easily our DPD shipping offer in your webshop.') . '<br>' .
            $this->l('This module supports the following shipping solutions:') . 
            '<ul>' .
                '<li>'. $this->l('DPD Classic') .'</li>' .
                '<li>'. $this->l('DPD Predict') .'</li>' .
                '<li>'. $this->l('DPD Pickup (delivery in a parcelshop chosen by your customer)') .'</li>' .
                '<li>'. $this->l('DPD Express (10:00, 12:00)') .'</li>' .
                '<li>'. $this->l('DPD Guarantee (18:00)') .'</li>' .
                '<li>'. $this->l('Compatibility with the default COD module from prestashop') .'</li>' .
            '</ul>' .
            $this->l('as well as:') .
            '<ul>' .
                '<li>'. $this->l('Track & trace for you and your customer on Order level. (1 link, all the parcels)') .'</li>' .
                '<li>'. $this->l('Add a return label to your RMA slip (and use the same T&T to follow it back)') .'</li>' .
            '</ul>';
        $additionalDescription = '<ul>' .
                '<li>'. $this->l('Generate the labels directly in the order or automatically (bulk) on a certain status.') .'</li>' .
                '<li>'. $this->l('Download them manually in the order or in bulk via the shipping list (1 PDF).') .'</li>' .
                '<li>'. $this->l('Print them in A6 or A4 (with full page cover).') .'</li>' .
            '</ul>' .
            $this->l('The add-on is free of charge, still a contract with DPD should be signed in order to have access to the DPD solutions.') .
            $this->l('You can use the in the configuration contact checklist and form to get everything going.');
        
        if (count($this->warnings) > 0) {
            $this->warning = '<br><ul>';
            foreach ($this->warnings as $warning) {
                $this->warning .= '<li>'.$warning.'</li>';
            }
            $this->warning .= '</ul>';
        }
        
        $this->loadHelper();
        DpdHelper::loadDis();
        
        $this->version = '0.2.0';
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
        $this->dependencies = array();
        $this->name = 'dpdcarrier';//DpdHelper::MODULENAME;
        $this->displayName = $this->l('DPD Carrier 2.0');
        $this->description = $this->l('Integrate easily our DPD shipping offer in your webshop.');
        $this->author = 'Michiel Van Gucht';
        $this->author_uri = 'https://be.linkedin.com/in/mvgucht';
        $this->description_full = $fullDescription;
        $this->additional_description = $additionalDescription;
        // This loads the module every time the back-end is loaded so we can check some stuff.
        $this->need_instance = 1;
        $this->tab = 'shipping_logistics';
        $this->limited_countries = array('be', 'lu', 'nl'); // Just to be a douche :)
        // $this->controllers = array('DpdStats', 'DpdConfig');  // This doesn't work.
        
        $this->bootstrap = true; // can't remember why. TODO: check this.
        
        parent::__construct();

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the DPD Carrier Module?');
        
        // This will check the module when it is called due to need_instance.
        if (self::isInstalled($this->name)) {
            
        }
        
    }
    
    /**
     *
     */
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
          
        if (!parent::install()) {
            $this->warnings[] = $this->l('Could not run parent installer');
            return false;
        }
        
        if (!DpdHelper::installDB()) {
            $this->warnings[] = $this->l('Could not add the needed database tables');
            return false;
        }
        
        if (!DpdHelper::initCarriers()) {
            $this->warnings[] = $this->l('Could not initialize the carriers');
            return false;
        }
        if (!DpdHelper::installTab()) {
            $this->warnings[] = $this->l('Could not add the shipping list tab');
            return false;
        }
        
        foreach ($this->hooks as $hook_name) {
            if (!$this->registerHook($hook_name)) {
                $this->warnings[] = $this->l('Could not register hook') . ' ' . $hook_name;
                return false;
            }
        }
        
        if (count(DpdHelper::installControllers($this->neededControllers)) > 0) {
            $this->warnings[] = $this->l('Could not register all controllers');
            return false;
        }
        
        if (!DpdHelper::createDPDLabelLocation()) {
            $this->warnings[] = $this->l('Could not create the download/dpd location on your filesystem');
            return false;
        }
        
        return true;
    }
    
    public function uninstall()
    {
        if (!DpdHelper::removeCarriers()) {
            $this->warnings[] = $this->l('Could not remove carriers');
        }
        
        if (!DpdHelper::uninstallTab()) {
            $this->warnings[] = $this->l('Could not remove tab');
        }

        foreach ($this->hooks as $hook_name) {
            if (!$this->unregisterHook($hook_name)) {
                $this->warnings[] = $this->l('Could not unhook hook') . ' ' . $hook_name;
            }
        }
        
        if (count(DpdHelper::uninstallControllers($this->neededControllers)) > 0) {
            $this->warnings[] = $this->l('Could not unregister all controllers');
        }
        
        if (!parent::uninstall()) {
            $this->warnings[] = $this->l('Could not run parent uninstaller successfully.');
        }
        
        $this->warnings[] = $this->l('The uninstaller doesn\'t automatically remove the label location on your file system');
        $this->warnings[] = $this->l('The uninstaller doesn\'t automatically remove the database tabels');
        
        return true;
    }
    
    /**
     *  The configuration screen content.
     */
    public function getContent()
    {
        $this->context->controller->addCSS($this->_path.'lib/DIS/templates/css/main.css');
        $this->context->controller->addJS($this->_path.'lib/DIS/js/dpdAdminConfig.js');
        
        $dpdTemplate = dirname(__FILE__) . DS.'lib'.DS.'DIS'.DS.'templates'.DS.'dpdAdminConfig.html';
        $handle = fopen($dpdTemplate, 'r');
        $content = fread($handle, filesize($dpdTemplate));
        fclose($handle);
        
        $this->context->smarty->assign(
            array(
                'configControllerUrl' => $this->context->link->getAdminLink('AdminDpdConfig') . "&ajax=true"
                ,'statsControllerUrl' => $this->context->link->getAdminLink('AdminDpdStats') . "&ajax=true"
            )
        );

        return $this->display($this->_path, '_dpdAdminConfigValues.tpl') . $content;
    }
    
    public function hookActionCarrierUpdate($params)
    {
        var_dump($params);
        die;
        
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
                ,'carrier_id' => Configuration::get(DpdHelper::generateVariableName('PICKUP_ID'))
                ,'container_id' => Configuration::get(DpdHelper::generateVariableName('LOC_CON_ID'))
            )
        );
        
        return $this->display($this->_path, '_dpdLocator.tpl');
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
            
            return $this->display($this->_path, '_frontOrderConfirmation.tpl');
        }
    }
    
    public function hookDisplayAdminOrderTabOrder($params)
    {
        if (DpdHelper::isDpdOrder($params['order'])) {
            return $this->display($this->_path, '_adminOrderTab.tpl');
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
            
            return $this->display($this->_path, '_adminOrderTabLabels16.tpl');
        }
    }
    
    public function hookActionOrderStatusUpdate($params)
    {
        $labelStatus = (int)Configuration::get(DpdHelper::generateVariableName('label on status'));
        if ($params['newOrderStatus']->id == $labelStatus) {
            $order = new Order($params['id_order']);
            $labels = DpdHelper::getOrderLabelInfo($order);
            if (count($labels) == 0) {
                DpdHelper::generateDefaultLabel($order);
            }
        }
    }
    
    public function hookDisplayPDFOrderReturn($params)
    {
        if ((bool)Configuration::get(DpdHelper::generateVariableName('RET_LABEL_ID'))) {
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
                
            return $this->display($this->_path, '_frontPDFOrderReturn.tpl');
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
}
