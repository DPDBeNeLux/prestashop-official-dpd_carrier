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
    }
    
    public function displayAjax()
    {
        if (Tools::getIsset('step')) {
            switch(Tools::getValue('step')) {
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
                    $this->saveUserCredentials();
                    $this->updateTTlink();
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
    
    private function testLogin()
    {
        if (!Tools::getIsset('delisid') || Tools::getValue('delisid') =='') {
            $this->output["validation"]["delisid"] = $this->module->l("Please enter your delisID.");
        }
        if (!Tools::getIsset('password') || Tools::getValue('password') =='') {
            $this->output["validation"]["password"] = $this->module->l("Please enter your password.");
        }
        
        DpdHelper::loadDis();
        
        if (count($this->output["validation"]) == 0) {
            $url = (bool)Tools::getIsset('dpd-live-account') ?
              'https://public-dis.dpd.nl/Services/' :
              'https://public-dis-stage.dpd.nl/Services/';
            
            $dpdLogin = new DisLogin(Tools::getValue('delisid'), Tools::getValue('password'), $url);
            // The constructor will use a cached value if available,
            // so to test the credentials we need to trigger a refresh
            if (!$dpdLogin->refreshed) {
                $dpdLogin->refresh();
            }
            if ($dpdLogin->getToken() == "") {
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
        if (!$return || $return != "Hello World!") {
            $this->output["warning"]["prestashop-cache"] = $this->module->l(
                "It looks like no cache is enabled on your system. " .
                "Please note that you'll need cache enabled when you start using the live services."
            );
        }
    }
    
    private function saveUserCredentials()
    {
        if (!Tools::getIsset('delisid') || Tools::getValue('delisid') =='') {
            $this->output["validation"]["delisid"] = $this->module->l("Please enter your delisID.");
        }
        if (!Tools::getIsset('password') || Tools::getValue('password') =='') {
            $this->output["validation"]["password"] = $this->module->l("Please enter your password.");
        }
        
        if (count($this->output["validation"]) == 0) {
            $id_shop_group = (int)$this->context->shop->getContextShopGroupID();
            $id_shop = (int)$this->context->shop->getContextShopID();
            
            Configuration::updateValue(DpdHelper::generateVariableName('delisid'), Tools::getValue('delisid'), $id_shop_group, $id_shop);
            Configuration::updateValue(DpdHelper::generateVariableName('password'), Tools::getValue('password'), $id_shop_group, $id_shop);
            Configuration::updateValue(DpdHelper::generateVariableName('live_server'), Tools::getIsset('dpd-live-account'), $id_shop_group, $id_shop);
            
            $this->output["success"]["dis-login-save"] = $this->module->l("User credentials saved.");
        } else {
            $this->output["error"]["dis-login-save"] = $this->module->l("User credentials couldn't be saved.");
        }
    }
    
    private function updateTTlink()
    {
        DpdHelper::loadDis();
        
        $shipping_services = new DisServices();
        foreach ($shipping_services->services as $service)
        {
            $carrier = new Carrier(Configuration::get(DpdHelper::generateVariableName($service->name . ' id')));
            $carrier->url = 'https://tracking.dpd.de/parcelstatus?locale=' . $this->context->language->iso_code . '_' . $this->context->country->iso_code .
                '&delisId=' . Tools::getValue('delisid') . 
                '&matchCode=@';
            $carrier->save();
        }
    }
    
    private function mailDisAccountRequest()
    {
        if (!Tools::getIsset('delisid') || Tools::getValue('delisid') =='') {
            $this->output["validation"]["delisid"] = $this->module->l("Please enter your delisID.");
        }
        if (!Tools::getIsset('password') || Tools::getValue('password') =='') {
            $this->output["validation"]["password"] = $this->module->l("Please enter your password.");
        }
        
        if (count($this->output["validation"]) == 0) {
            $id_lang = Language::getIdByIso("EN");//Tools::getValue('country'));
            $subject = '[' . Tools::getValue('country') . '] New DIS account request: ' . Tools::getValue('delisid');
            
            $template_vars = array(
              '{delisid}' => Tools::getValue('delisid')
            );
            
            if (Mail::Send(
                $id_lang,
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
        if (!Tools::getIsset('company') || Tools::getValue('company') == '') {
            $this->output["validation"]["company"] = $this->module->l("Please enter your company name.");
        }
        if (!Tools::getIsset('ppm') || Tools::getValue('ppm') =='') {
            $this->output["validation"]["ppm"] = $this->module->l(
                "Please enter the amount of parcels you are " .
                "(planning on) shipping a month."
            );
        }
        if (!Tools::getIsset('contact') || Tools::getValue('contact') =='') {
            $this->output["validation"]["contact"] = $this->module->l("Please enter your name.");
        }
        if (!Tools::getIsset('email') || Tools::getValue('email') =='') {
            $this->output["validation"]["email"] = $this->module->l("Please enter your email.");
        }
        if (!Tools::getIsset('phone') || Tools::getValue('phone') =='') {
            $this->output["validation"]["phone"] = $this->module->l("Please enter your phone number.");
        }
        if (!Tools::getIsset('street') || Tools::getValue('street') =='') {
            $this->output["validation"]["street"] = $this->module->l("Please enter your street.");
        }
        if (!Tools::getIsset('houseno') || Tools::getValue('houseno') =='') {
            $this->output["validation"]["houseno"] = $this->module->l("Please enter your house number.");
        }
        if (!Tools::getIsset('country') || Tools::getValue('country') =='') {
            $this->output["validation"]["country"] = $this->module->l("Please select your country.");
        }
        if (!Tools::getIsset('postcode') || Tools::getValue('postcode') =='') {
            $this->output["validation"]["postcode"] = $this->module->l("Please enter your postal code.");
        }
        if (!Tools::getIsset('city') || Tools::getValue('city') =='') {
            $this->output["validation"]["city"] = $this->module->l("Please enter your city.");
        }
        
        if (count($this->output["validation"]) == 0) {
            $id_lang = Language::getIdByIso("EN");//Tools::getValue('country'));
            $subject = '[' . Tools::getValue('country') . '] New account request: ' . Tools::getValue('company');
            
            $template_vars = array(
              '{company}' => Tools::getValue('company')
              ,'{ppm}' => Tools::getValue('ppm')
              ,'{contact}' => Tools::getValue('contact')
              ,'{email}' => Tools::getValue('email')
              ,'{phone}' => Tools::getValue('phone')
              ,'{address}' => Tools::getValue('street') . " " . Tools::getValue('houseno')
              ,'{country}' => Tools::getValue('country')
              ,'{postal}' => Tools::getValue('postcode')
              ,'{city}' => Tools::getValue('city')
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
