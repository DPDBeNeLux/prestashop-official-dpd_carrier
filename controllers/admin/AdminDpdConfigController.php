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
        
        $this->module->loadDis();
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
                    $this->testCache();
                    $this->testLogin();
                    $this->saveUserCredentials();
                    break;
                default:
                    Tools::Redirect(__PS_BASE_URI__);
                    break;
            }
        }
        echo Tools::jsonEncode($this->output);
        die;
    }
    
    private function testLogin()
    {
        if (!Tools::getIsset('delisid') || Tools::getValue('delisid') =='') {
            $this->output["validation"]["delisid"] = "Please enter your delisID.";
        }
        if (!Tools::getIsset('password') || Tools::getValue('password') =='') {
            $this->output["validation"]["password"] = "Please enter your password.";
        }

        if (count($this->output["validation"]) == 0) {
            $url = (bool)Tools::getValue('password') ? 'https://public-dis.dpd.nl/Services/' : 'https://public-dis-stage.dpd.nl/Services/';
            
            $dpdLogin = new DisLogin(Tools::getValue('delisid'), Tools::getValue('password'), $url);
            // The constructor will use a cached value if available, so to test the credentials we need to trigger a refresh/
            if (!$dpdLogin->refreshed) {
                $dpdLogin->refresh();
            }
            if ($dpdLogin->getToken() == "") {
                $this->output["warning"]["dis-login"] = "Seems like your user name and password don't work. Perhaps you can try it on the other server?";
            } else {
                $this->output["success"]["dis-login"] = "The login test worked, so you should be ready to go.";
                if (!Tools::getIsset('dpd-live-account')) {
                    $this->output["info"]["stage-account"] = "Please note that you selected to connect to the Stage/Test server. Do not use this to send out real parcels, only use it to check your setup.";
                }
            }
        }

    }
    
    private function testCache(){
        // _PS_CACHING_SYSTEM_ keeps set after disabling in the back end.
        $cache = Cache::getInstance();
        $cache->set("DPDTest", "Hello World!", 60);
        $return = $cache->get("DPDTest");
        if(!$return || $return != "Hello World!") {
            $this->output["warning"]["prestashop-cache"] = "It looks like no cache is enabled on your system. Please note that you'll need cache enabled when you start using the live services.";
        }
    }
    
    private function saveUserCredentials()
    {
        if (!Tools::getIsset('delisid') || Tools::getValue('delisid') =='') {
            $this->output["validation"]["delisid"] = "Please enter your delisID.";
        }
        if (!Tools::getIsset('password') || Tools::getValue('password') =='') {
            $this->output["validation"]["password"] = "Please enter your password.";
        }
        
        if (count($this->output["validation"]) == 0) {
            Configuration::updateValue('DPD_DIS_delisid', Tools::getValue('delisid'));
            Configuration::updateValue('DPD_DIS_password', Tools::getValue('password'));
            if (!Tools::getIsset('dpd-live-account')) {
                Configuration::updateValue('DPD_DIS_live_server', true);
            }
            $this->output["success"]["dis-login-save"] = "User credentials saved.";
        } else {
            $this->output["error"]["dis-login-save"] = "User credentials couldn't be saved.";
        }
    }
    
    private function mailDisAccountRequest()
    {
        if (!Tools::getIsset('delisid') || Tools::getValue('delisid') =='') {
            $this->output["validation"]["delisid"] = "Please enter your delisID.";
        }
        if (!Tools::getIsset('password') || Tools::getValue('password') =='') {
            $this->output["validation"]["password"] = "Please enter your password.";
        }
        
        if (count($this->output["validation"]) == 0) {
            $id_lang = Language::getIdByIso("GB");//Tools::getValue('country'));
            $subject = '[' . Tools::getValue('country') . '] New DIS account request: ' . Tools::getValue('delisid');
            
            $template_vars = array(
              '{delisid}' => Tools::getValue('delisid')
            );
            
            if (Mail::Send(
                $id_lang,
                'DIS_account_request',
                $subject,
                $template_vars,
                'michiel.vangucht@dpd.be',
                null,
                null,
                null,
                null,
                null,
                $this->mailTemplateUrl,
                false,
                $this->context->shop->id
            )) {
                $this->output["success"]["dis-account-request"] = "Your account request has been send";
            } else {
                $this->output["error"]["dis-account-request"] = "Your account request couldn't be send. Please check your log for more information";
            }
            
        } else {
            $this->output["error"]["dis-account-request"] = "Your account request couldn't be generated";
        }
    }
    
    private function mailAccountRequest()
    {
        if (!Tools::getIsset('company') || Tools::getValue('company') == '') {
            $this->output["validation"]["company"] = "Please enter your company name.";
        }
        if (!Tools::getIsset('ppm') || Tools::getValue('ppm') =='') {
            $this->output["validation"]["ppm"] = "Please enter the amount of parcels you are (planning on) shipping a month.";
        }
        if (!Tools::getIsset('contact') || Tools::getValue('contact') =='') {
            $this->output["validation"]["contact"] = "Please enter your name.";
        }
        if (!Tools::getIsset('email') || Tools::getValue('email') =='') {
            $this->output["validation"]["email"] = "Please enter your email.";
        }
        if (!Tools::getIsset('phone') || Tools::getValue('phone') =='') {
            $this->output["validation"]["phone"] = "Please enter your phone number.";
        }
        if (!Tools::getIsset('street') || Tools::getValue('street') =='') {
            $this->output["validation"]["street"] = "Please enter your street.";
        }
        if (!Tools::getIsset('houseno') || Tools::getValue('houseno') =='') {
            $this->output["validation"]["houseno"] = "Please enter your house number.";
        }
        if (!Tools::getIsset('country') || Tools::getValue('country') =='') {
            $this->output["validation"]["country"] = "Please select your country.";
        }
        if (!Tools::getIsset('postcode') || Tools::getValue('postcode') =='') {
            $this->output["validation"]["postcode"] = "Please enter your postal code.";
        }
        if (!Tools::getIsset('city') || Tools::getValue('city') =='') {
            $this->output["validation"]["city"] = "Please enter your city.";
        }
        
        if (count($this->output["validation"]) == 0) {
            $id_lang = Language::getIdByIso("GB");//Tools::getValue('country'));
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
                'michiel.vangucht@dpd.be',
                null,
                null,
                null,
                null,
                null,
                $this->mailTemplateUrl,
                false,
                $this->context->shop->id
            )) {
                $this->output["success"]["dpd-account-request"] = "Your account request has been send";
            } else {
                $this->output["error"]["dpd-account-request"] = "Your account request couldn't be send. Please check your log for more information";
            }
        } else {
            $this->output["error"]["dpd-account-request"] = "Your account request couldn't be generated";
        }
    }
}
