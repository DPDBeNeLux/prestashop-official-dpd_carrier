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

class AdminDpdConfigController extends ModuleAdminController
{
    private $output = array(
        "success" => array()
        ,"info" => array()
        ,"warning" => array()
        ,"error" => array()
        ,"validation" => array()
    );
    
    public function __construct()
    {
        parent::__construct();
        $this->mailTemplateUrl = _PS_MODULE_DIR_ . $this->module->name . "/mails/";
        
        $this->module->loadHelper();
        
        $this->id_shop_group = (int)$this->context->shop->getContextShopGroupID();
        $this->id_shop = (int)$this->context->shop->getContextShopID();
    }
    
    public function displayAjax()
    {
        if (Tools::getIsset('settings')) {
            $this->getCurrentSettings();
        } elseif (Tools::getIsset('states')) {
            $this->getOrderStates();
        } elseif (Tools::getIsset('step')) {
            switch((int)Tools::getValue('step')) {
                case 0:
                    $this->mailAccountRequest();
                    break;
                case 1:
                    $this->mailDisAccountRequest();
                    $this->saveUserCredentials();
                    break;
                case 2:
                    // TODO: Create testSenderAddress !!!
                    $this->testCache();
                    $this->testLogin();
                    $this->testSenderAddress();
                    $this->saveUserCredentials();
                    $this->updateTTlink();
                    $this->saveAdvancedConfiguration();
                    break;
                default:
                    Tools::Redirect(__PS_BASE_URI__);
                    break;
            }
        } else {
            Tools::Redirect(__PS_BASE_URI__);
            die;
        }
        echo Tools::jsonEncode($this->output);
        die;
    }
    
    private function getCurrentSettings()
    {
        $result = array();
        
        $delisid = (string)Configuration::get((string)DpdHelper::generateVariableName('delisid'));
        if ($delisid) {
            $result['delisid'] = $delisid;
        }
        
        $live_server = (bool)Configuration::get((string)DpdHelper::generateVariableName('live_server'));
        if ($live_server) {
            $result['dpd-live-account'] = $live_server;
        }
        
        $container_id = (string)Configuration::get((string)DpdHelper::generateVariableName('LOC_CON_ID'));
        if ($container_id) {
            $result['locator-container-id'] = $container_id;
        }
        
        $return_label = (bool)Configuration::get((string)DpdHelper::generateVariableName('RET_LABEL'));
        if ($return_label) {
            $result['dpd-return-label'] = $return_label;
        }
        
        $auto_label = (string)Configuration::get((string)DpdHelper::generateVariableName('label on status'));
        if ($auto_label) {
            $result['dpd-label-on-state'] = $auto_label;
        }
        
        $label_format = (string)Configuration::get((string)DpdHelper::generateVariableName('label format'));
        if ($label_format) {
            $result['dpd-label-format'] = $label_format;
        }
        
        $this->output['success'] = $result;
    }
    
    private function getOrderStates()
    {
        $id_lang = $this->context->language->id;
        
        $order_states = array();
        
        foreach (OrderState::getOrderStates($id_lang) as $orderState) {
            $order_states[$orderState['id_order_state']] = $orderState['name'];
        }
        
        $this->output['success'] = $order_states;
    }
    
    private function testLogin()
    {
        if (!(bool)Tools::getIsset('delisid') || (string)Tools::getValue('delisid') =='') {
            $this->output["validation"]["delisid"] = $this->module->l("Please enter your delisID.");
        }
        if (!(bool)Tools::getIsset('password') || (string)Tools::getValue('password') =='') {
            $this->output["validation"]["password"] = $this->module->l("Please enter your password.");
        }
        
        DpdHelper::loadDis();
        
        if (count($this->output["validation"]) == 0) {
            $url = (bool)Tools::getIsset('dpd-live-account') ?
              'https://public-dis.dpd.nl/Services/' :
              'https://public-dis-stage.dpd.nl/Services/';
            
            $dpdLogin = new DisLogin((string)Tools::getValue('delisid'), (string)Tools::getValue('password'), $url);
            // The constructor will use a cached value if available,
            // so to test the credentials we need to trigger a refresh
            if (!$dpdLogin->refreshed) {
                $dpdLogin->refresh();
            }
            if ((string)$dpdLogin->getToken() == "") {
                $this->output["warning"]["dis-login"] = $this->module->l(
                    "Seems like your user name and password don't work. " .
                    "Perhaps you can try it on the other server?"
                );
            } else {
                $this->output["success"]["dis-login"] = $this->module->l(
                    "The login test worked, so you should be ready to go."
                );
                if (!Tools::getIsset('dpd-live-account')) {
                    $this->output["info"]["stage-account"] = $this->module->l(
                        "Please note that you selected to connect to the Stage/Test server. " .
                        "Do not use this to send out real parcels, only use it to check your setup."
                    );
                }
            }
        }

    }
    
    private function testCache()
    {
        // _PS_CACHING_SYSTEM_ keeps set after disabling in the back end.
        $cache = Cache::getInstance();
        $cache->set("DPDTest", "Hello World!", 60);
        $return = $cache->get("DPDTest");
        if (!$return || (string)$return != "Hello World!") {
            $this->output["warning"]["prestashop-cache"] = $this->module->l(
                "It looks like no cache is enabled on your system. " .
                "Please note that you'll need cache enabled when you start using the live services."
            );
        }
    }
    
    private function testSenderAddress()
    {
        $address = DpdHelper::getSenderAddress();
        
        if (empty($address->iso_A2)
            || empty($address->postcode)
            || empty($address->city)) {
            $link = new Link();
            $this->output["warning"]["dis-login"] = $this->module->l('Seems like you haven\'t defined a shop contact address.') .
                '<br>' . $this->module->l('Please configure one before you try to generate a label. (it will fail)') . '<br>' .
                '<a href="' . $link->getAdminLink('AdminStores') . '">' . $this->module->l('Store Contacts') . '</a>';
        }
    }
    
    private function saveUserCredentials()
    {
        if (!(bool)Tools::getIsset('delisid') || (string)Tools::getValue('delisid') =='') {
            $this->output["validation"]["delisid"] = $this->module->l("Please enter your delisID.");
        }
        if (!(bool)Tools::getIsset('password') || (string)Tools::getValue('password') =='') {
            $this->output["validation"]["password"] = $this->module->l("Please enter your password.");
        }
        
        if (count($this->output["validation"]) == 0) {
            Configuration::updateValue(
                (string)DpdHelper::generateVariableName('delisid'),
                (string)Tools::getValue('delisid'),
                $this->id_shop_group,
                $this->id_shop
            );
            Configuration::updateValue(
                (string)DpdHelper::generateVariableName('password'),
                (string)Tools::getValue('password'),
                $this->id_shop_group,
                $this->id_shop
            );
            Configuration::updateValue(
                (string)DpdHelper::generateVariableName('live_server'),
                (bool)Tools::getIsset('dpd-live-account'),
                $this->id_shop_group,
                $this->id_shop
            );
            
            $this->output["success"]["dis-login-save"] = $this->module->l("User credentials saved.");
        } else {
            $this->output["error"]["dis-login-save"] = $this->module->l("User credentials couldn't be saved.");
        }
    }
    
    private function updateTTlink()
    {
        DpdHelper::loadDis();
        
        $shipping_services = new DisServices();
        foreach ($shipping_services->services as $service) {
            $configuration_name = (string)DpdHelper::generateVariableName($service->name . ' id');
            $carrier = new Carrier((int)Configuration::get($configuration_name));
            if (!empty($carrier->id)) {
                $carrier->url = 'https://tracking.dpd.de/parcelstatus?locale=' .
                    (string)$this->context->language->iso_code . '_' .
                    (string)$this->context->country->iso_code .
                    '&delisId=' . (string)Tools::getValue('delisid') .
                    '&matchCode=@';
                $carrier->save();
            }
        }
    }
    
    private function saveAdvancedConfiguration()
    {
        $status = $this->module->l('default');
        if ((string)Tools::getValue('locator-container-id') != '') {
            $status = (string)Tools::getValue('locator-container-id');
        }
        
        Configuration::updateValue(
            (string)DpdHelper::generateVariableName('LOC_CON_ID'),
            (string)Tools::getValue('locator-container-id'),
            $this->id_shop_group,
            $this->id_shop
        );
        $this->output["success"]["locator-container-id"] = $this->module->l('Locator container ID set to') . ' ' . $status;

        Configuration::updateValue(
            (string)DpdHelper::generateVariableName('RET_LABEL'),
            (bool)Tools::getIsset('dpd-return-label'),
            $this->id_shop_group,
            $this->id_shop
        );
        $status;
        if ((bool)Tools::getIsset('dpd-return-label')) {
            $status = $this->module->l('enabled');
        } else {
            $status = $this->module->l('disabled');
        }
        
        $this->output["success"]["dpd-return-label"] = $this->module->l('Return label in RMA slip is') . ' ' . $status;
        
        
        $status;
        if ((int)Tools::getValue('dpd-label-on-state') == 0) {
            $status = $this->module->l('won\'t be generated automatically');
        } else {
            $orderState = new OrderState((int)Tools::getValue('dpd-label-on-state'));
            $status = $this->module->l('will be generated automatically on status') .
                ' ' . $orderState->name[$this->context->language->id];
        }
        
        Configuration::updateValue(
            (string)DpdHelper::generateVariableName('label on status'),
            (int)Tools::getValue('dpd-label-on-state'),
            $this->id_shop_group,
            $this->id_shop
        );
        $this->output["success"]["dpd-label-on-state"] = $this->module->l('Labels (for DPD orders)') . ' ' . $status;
        
        Configuration::updateValue(
            (string)DpdHelper::generateVariableName('label format'),
            (string)Tools::getValue('dpd-label-format'),
            $this->id_shop_group,
            $this->id_shop
        );
        $this->output["success"]["dpd-label-format"] = $this->module->l('Label format is set to') .
            ' ' . (string)Tools::getValue('dpd-label-format');
    }
    
    private function mailDisAccountRequest()
    {
        if (!(bool)Tools::getIsset('delisid') || (string)Tools::getValue('delisid') == '') {
            $this->output["validation"]["delisid"] = $this->module->l("Please enter your delisID.");
        }
        if (!(bool)Tools::getIsset('password') || (string)Tools::getValue('password') =='') {
            $this->output["validation"]["password"] = $this->module->l("Please enter your password.");
        }
        
        if (count($this->output["validation"]) == 0) {
            $id_lang = (string)Language::getIdByIso("EN");//Tools::getValue('country'));
            $subject = '[' . (string)Tools::getValue('country') . '] New DIS account request: ' . (string)Tools::getValue('delisid');
            
            $template_vars = array(
              '{delisid}' => (string)Tools::getValue('delisid')
            );
            
            if (Mail::Send(
                (string)$id_lang,
                'DIS_account_request',
                $subject,
                $template_vars,
                'prestashop@dpd.be',
                null,
                null,
                null,
                null,
                null,
                $this->mailTemplateUrl,
                false,
                $this->context->shop->id
            )) {
                $this->output["success"]["dis-account-request"] = $this->module->l(
                    "Your account request has been send"
                );
            } else {
                $this->output["error"]["dis-account-request"] = $this->module->l(
                    "Your account request couldn't be send. " .
                    "Please check your log for more information"
                );
            }
            
        } else {
            $this->output["error"]["dis-account-request"] = $this->module->l(
                "Your account request couldn't be generated"
            );
        }
    }
    
    private function mailAccountRequest()
    {
        if (!(bool)Tools::getIsset('company') || (string)Tools::getValue('company') == '') {
            $this->output["validation"]["company"] = $this->module->l("Please enter your company name.");
        }
        if (!(bool)Tools::getIsset('ppm') || (string)Tools::getValue('ppm') == '') {
            $this->output["validation"]["ppm"] = $this->module->l(
                "Please enter the amount of parcels you are " .
                "(planning on) shipping a month."
            );
        }
        if (!(bool)Tools::getIsset('contact') || (string)Tools::getValue('contact') =='') {
            $this->output["validation"]["contact"] = $this->module->l("Please enter your name.");
        }
        if (!(bool)Tools::getIsset('email') || (string)Tools::getValue('email') =='') {
            $this->output["validation"]["email"] = $this->module->l("Please enter your email.");
        }
        if (!(bool)Tools::getIsset('phone') || (string)Tools::getValue('phone') =='') {
            $this->output["validation"]["phone"] = $this->module->l("Please enter your phone number.");
        }
        if (!(bool)Tools::getIsset('street') || (string)Tools::getValue('street') =='') {
            $this->output["validation"]["street"] = $this->module->l("Please enter your street.");
        }
        if (!(bool)Tools::getIsset('houseno') || (string)Tools::getValue('houseno') =='') {
            $this->output["validation"]["houseno"] = $this->module->l("Please enter your house number.");
        }
        if (!(bool)Tools::getIsset('country') || (string)Tools::getValue('country') =='') {
            $this->output["validation"]["country"] = $this->module->l("Please select your country.");
        }
        if (!(bool)Tools::getIsset('postcode') || (string)Tools::getValue('postcode') =='') {
            $this->output["validation"]["postcode"] = $this->module->l("Please enter your postal code.");
        }
        if (!(bool)Tools::getIsset('city') || (string)Tools::getValue('city') =='') {
            $this->output["validation"]["city"] = $this->module->l("Please enter your city.");
        }
        
        if (count($this->output["validation"]) == 0) {
            $id_lang = (string)Language::getIdByIso("EN");//Tools::getValue('country'));
            $subject = '[' . (string)Tools::getValue('country') . '] New account request: ' . (string)Tools::getValue('company');
            
            $template_vars = array(
              '{company}' => (string)Tools::getValue('company')
              ,'{ppm}' => (string)Tools::getValue('ppm')
              ,'{contact}' => (string)Tools::getValue('contact')
              ,'{email}' => (string)(string)Tools::getValue('email')
              ,'{phone}' => (string)Tools::getValue('phone')
              ,'{address}' => (string)Tools::getValue('street') . " " . (string)Tools::getValue('houseno')
              ,'{country}' => (string)Tools::getValue('country')
              ,'{postal}' => (string)Tools::getValue('postcode')
              ,'{city}' => (string)Tools::getValue('city')
            );
            
            if (Mail::Send(
                $id_lang,
                'account_request',
                $subject,
                $template_vars,
                'prestashop@dpd.be',
                null,
                null,
                null,
                null,
                null,
                $this->mailTemplateUrl,
                false,
                $this->context->shop->id
            )) {
                $this->output["success"]["dpd-account-request"] = $this->module->l(
                    "Your account request has been send"
                );
            } else {
                $this->output["error"]["dpd-account-request"] = $this->module->l(
                    "Your account request couldn't be send. " .
                    "Please check your log for more information"
                );
            }
        } else {
            $this->output["error"]["dpd-account-request"] = $this->module->l(
                "Your account request couldn't be generated"
            );
        }
    }
}
