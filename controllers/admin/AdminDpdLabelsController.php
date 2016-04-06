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

class AdminDpdLabelsController extends ModuleAdminController
{
    private $output = array(
        "success" => array()
        ,"info" => array()
        ,"warning" => array()
        ,"error" => array()
        ,"validation" => array()
    );
    
    public function delete() {
        
    }
    
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'dpdcarrier_label';
        $this->className = 'AdminDpdLabelsController';
        $this->lang = false;
        $this->export = true;
        
        $this->fields_list = array(
            'id_order' => array('title' => $this->l('Order'), 'class' => 'fixed-width-xs')
            ,'parcel_number' => array('title' => $this->l('Parcel Number'))
            ,'weight' => array('title' => $this->l('Weight'), 'class' => 'fixed-width-xs')
            ,'length' => array('title' => $this->l('Length'), 'class' => 'fixed-width-xs')
            ,'height' => array('title' => $this->l('Height'), 'class' => 'fixed-width-xs')
            ,'depth' => array('title' => $this->l('Depth'), 'class' => 'fixed-width-xs')
            ,'value' => array('title' => $this->l('Value'), 'class' => 'fixed-width-xs')
            ,'id_location' => array('title' => $this->l('Location'), 'class' => 'fixed-width-xs')
            ,'address' => array('title' => $this->l('Address'))
            ,'services' => array('title' => $this->l('Services'), 'class' => 'fixed-width-xs')
        );
        
        $this->bulk_actions = array(
            'print' => array(
                'text' => $this->l('Print selected'),
                'icon' => 'icon-print'
            )
            ,'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon' => 'icon-trash'
            )
        );
        
        parent::__construct();
    }
    
    public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
    {
        $this->context->shop->getContextShopGroupID();
        $this->context->shop->getContextShopGroupID();
        
        $query = new DbQuery();
        $query->select('*')
            ->from('dpdcarrier_label', 'psdl')
            ->leftJoin('orders', 'pso', 'psdl.id_order = pso.id_order')
            ->leftJoin('address', 'psa', 'pso.id_address_delivery = psa.id_address')
            ->where('shipped <> 1');
            
        if (Shop::getContext() == Shop::CONTEXT_GROUP || Shop::getContext() == Shop::CONTEXT_SHOP) {
            if (Shop::getContext() == Shop::CONTEXT_GROUP) {
                $query->where('id_shop_group = ' . (int)$this->context->shop->getContextShopGroupID());
            } elseif (Shop::getContext() == Shop::CONTEXT_SHOP) {
                $query->where('id_shop = ' . (int)$this->context->shop->getContextShopID());
            }
        }
        
        $data = Db::getInstance()->executeS($query);
        
        if ($data) {
            foreach($data as $key => $row) {
                $services = unserialize($data[$key]['services']);
                $service_output = "";
                foreach($services as $name => $bool) {
                    if($bool) {
                        $service_output .= " " . $name;
                    }
                }
                
                $data[$key]['services'] = $service_output;
                
                $data[$key]['address'] = $data[$key]['firstname'] . " " . $data[$key]['lastname'] . " " . $data[$key]['address1'] . " " . $data[$key]['postcode'] . " " . $data[$key]['city'];
            }
        }
        $this->_list = $data;
        $this->_listTotal = count($data);
    }
    
    public function renderOptions()
    {
        // Set toolbar options
        $this->display = 'options';
        $this->show_toolbar = true;
        $this->toolbar_scroll = true;
        $this->initToolbar();

        return parent::renderOptions();
    }

    public function initToolbar()
    {
         $this->toolbar_btn['print'] = array(
            'href' => 'test',
            'desc' => $this->l('Automatically print the shippinglist for labels that haven\t been printed before.')
        );
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }
    
    public function displayAjax()
    {
        if (Tools::getIsset('action')) {
            switch(Tools::getValue('action')) {
                case 'download':
                    $this->downloadLabels();
                    break;
                case 'generate':
                    if (!Tools::getIsset('label_count') || (int)Tools::getValue('label_count') <= 0 ) {
                        $this->output["validation"]["label_count"] = "Please enter the amount of labels you need.";
                    }
                    if (!Tools::getIsset('label_weight') || (float)Tools::getValue('label_weight') < 0) {
                        $this->output["validation"]["label_weight"] = "Please enter the weight of the parcels.";
                    }
                    if (!Tools::getIsset('id_order') || Tools::getValue('id_order') == '') {
                        $this->output["validation"]["id_order"] = "Couldn't determine the order you are in.";
                    }
                    
                    if (count($this->output["validation"]) == 0) {
                        $order = new Order(Tools::getValue('id_order'));
                        $label_settings['count'] = (int)Tools::getValue('label_count');
                        
                        if (Tools::getIsset('label_weight')) {
                            $label_settings['weight'] = (float)Tools::getValue('label_weight');
                        }
                        
                        if (Tools::getIsset('label_length')
                            && Tools::getIsset('label_height')
                            && Tools::getIsset('label_depth')) {
                                $label_settings['length'] = (float)Tools::getValue('label_length');
                                $label_settings['height'] = (float)Tools::getValue('label_height');
                                $label_settings['depth'] = (float)Tools::getValue('label_depth');
                        }
                        
                        if (Tools::getIsset('label_value') && (float)Tools::getValue('label_value') > 0) {
                            $label_settings['value'] = (float)Tools::getValue('label_value');
                        }
                        
                        if (Tools::getIsset('label_ps_id') && Tools::getValue('label_ps_id') != '') {
                            $label_settings['id_location'] = (string)Tools::getValue('label_ps_id');
                        }
                        

                        $label_settings['cod'] = Tools::getIsset('cod_delivery');
                        $label_settings['dps'] = Tools::getIsset('dps_delivery');
                        $label_settings['predict'] = Tools::getIsset('predict_delivery');
                        $label_settings['sat'] = Tools::getIsset('sat_delivery');
                        $label_settings['comp'] = Tools::getIsset('comp_delivery');
                        $label_settings['e10'] = Tools::getIsset('e10_delivery');
                        $label_settings['e12'] = Tools::getIsset('e12_delivery');
                        $label_settings['e18'] = Tools::getIsset('e18_delivery');
                        
                        $this->generateLabels($order, $label_settings);
                    }
                    break;
                case 'info':
                    $this->infoLabels();
                    break;
                default:
                    Tools::redirect(__PS_BASE_URI__);
                    die;
                    break;
            }
        } else {
            Tools::redirect(__PS_BASE_URI__);
            die;
        }
        
        echo Tools::jsonEncode($this->output);
        die;
    }
    
    private function getSenderAddress()
    {
        $result = new StdClass();
        $result->company = Configuration::get('PS_SHOP_NAME');
        $result->address1 = Configuration::get('PS_SHOP_ADDR1');
        $result->iso_A2 = Country::getIsoById(Configuration::get('PS_SHOP_COUNTRY_ID'));
        $result->postcode = Configuration::get('PS_SHOP_CODE');
        $result->city = Configuration::get('PS_SHOP_CITY');
        
        return $result;
    }
    
    private function copyArray($source)
    {
        $result = array();
        
        foreach($source as $key => $item){
            $result[$key] = (is_array($item) ? $this->copyArray($item) : $item);
        }
        
        return $result;
    }
    
    public function generateDefaultLabel($id_order)
    {
        $order = new Order($id_order);
        $order_carrier = new OrderCarrier($order->getIdOrderCarrier());
        $cart = new Cart($order->id_cart);
        $shop_info = $this->module->getParcelShopInfo($cart);
        
        $label_settings = $this->module->getInitialOrderSettings($order);
        $label_settings['count'] = 1;
        $label_settings['weight'] = $order_carrier->weight;
        $label_settings['length'] = 0;
        $label_settings['height'] = 0;
        $label_settings['depth'] = 0;
        $label_settings['id_location'] = $shop_info['id_location'];
        $label_settings['value'] = $order->total_paid;
        
        $this->generateLabels($order, $label_settings);
    }
    
    private function generateLabels($order, $label_settings)
    {
        if (DpdCarrier::isDpdOrder($order)) {
            $current_carrier = new Carrier($order->id_carrier);
            $order_carrier = new OrderCarrier($order->getIdOrderCarrier());
            
            $disLogin = $this->module->getLogin();
            
            if ($disLogin) {
                $this->module->loadDis();
                
                $sender_address = $this->getSenderAddress();
                $recipient_address = new Address($order->id_address_delivery);
                $recipient_customer = new Customer($order->id_customer);
                
                $shipment = new DisShipment($disLogin);
                
                $phone_number = isset($recipient_address->phone_mobile) && $recipient_address->phone_mobile != '' ?
                    $recipient_address->phone_mobile :
                    $recipient_address->phone;
                
                $shipment->request['order'] = array(
                    'generalShipmentData' => array(
                        'mpsCustomerReferenceNumber1' => $order->reference
                        ,'sendingDepot' => $disLogin->getDepot()
                        ,'product' => 'CL'
                        ,'sender' => array(
                            'name1' => $sender_address->company
                            ,'street' => $sender_address->address1
                            ,'country' => $sender_address->iso_A2
                            ,'zipCode' => $sender_address->postcode
                            ,'city' => $sender_address->city
                        )
                        ,'recipient' => array(
                            'name1' => Tools::substr(
                                $recipient_address->firstname . ' ' .
                                $recipient_address->lastname . ' ' .
                                $recipient_address->company,
                                0,
                                35
                            )
                            ,'name2' => $recipient_address->address2
                            ,'street' => $recipient_address->address1
                            ,'country' => Country::getIsoById($recipient_address->id_country)
                            ,'zipCode' => $recipient_address->postcode
                            ,'city' => $recipient_address->city
                            ,'contact' => $phone_number
                            ,'phone' =>$phone_number
                            ,'email' => $recipient_customer->email
                        )
                    )
                    ,'productAndServiceData' => array(
                        'orderType' => 'consignment'
                    )
                );
                
                $shipment->request['order']['parcels']['customerReferenceNumber1'] = $order->reference;
                if (isset($label_settings['weight']) && (float)$label_settings['weight'] != 0) {
                      $shipment->request['order']['parcels']['weight'] = (int)($label_settings['weight'] * (100 / $this->module->getWeightMultiplier()));
                }
                
                if (isset($label_settings['length']) && $label_settings['length'] != 0
                    && isset($label_settings['height']) && $label_settings['height'] != 0
                    && isset($label_settings['depth']) && $label_settings['depth'] != 0) {
                    
                    $multiplier = $this->module->getDimensionMultiplier();
                    
                    $length = str_pad(
                        (int)($label_settings['length'] / $multiplier)
                        , 3
                        , '0'
                        , STR_PAD_LEFT
                    );
                    
                    $height = str_pad(
                        (int)($label_settings['height'] / $multiplier)
                        , 3
                        , '0'
                        , STR_PAD_LEFT
                    );
                    
                    $depth = str_pad(
                        (int)($label_settings['depth'] / $multiplier)
                        , 3
                        , '0'
                        , STR_PAD_LEFT
                    );
                    
                    $shipment->request['order']['parcels']['volume'] = $length . $height . $depth; 
                }
                
                $label_count = (int)$label_settings['count'];
                
                if (isset($label_settings['cod'])  && $label_settings['cod'] 
                    && isset($label_settings['value']) && (float)$label_settings['value'] > 0) {
                        $currency = new Currency($order->id_currency);
                        $shipment->request['order']['parcels']['cod'] = array(
                            'amount' => (int)((float)$label_settings['value'] * 100)
                            ,'currency' => Tools::strtoupper($currency->iso_code)
                            ,'inkasso' => 0
                            
                        );
                        
                        if ($label_count > 1) {
                            $parcel_data = $shipment->request['order']['parcels'];
                            $shipment->request['order']['parcels'] = array();
                            for ($i = 0; $i < $label_count; $i++) {
                                $shipment->request['order']['parcels'][] = $this->copyArray($parcel_data);
                            }
                            
                            $label_count = 1;
                        }
                }
                
                $notification_lang_ISO = Language::getIsoById($order->id_lang);
                $notification = $this->getNotificationData($recipient_customer, $notification_lang_ISO);
                
                if (isset($label_settings['dps']) && $label_settings['dps'] 
                    && isset($label_settings['id_location']) && $label_settings['id_location'] != '') {
                        $shipment->request['order']['productAndServiceData']['parcelShopDelivery']['parcelShopId'] = $label_settings['id_location'];
                        $shipment->request['order']['productAndServiceData']['parcelShopDelivery']['parcelShopNotification'] = $notification;
                }
                
                if (isset($label_settings['predict']) && $label_settings['predict']) {
                    $shipment->request['order']['productAndServiceData']['predict'] = $notification;
                }
                
                if (isset($label_settings['sat']) && $label_settings['sat']) {
                    $shipment->request['order']['productAndServiceData']['saturdayDelivery'] = true;
                }
                
                if (isset($label_settings['comp']) && $label_settings['comp']) {
                    $shipment->request['order']['generalShipmentData']['mpsCompleteDelivery'] = true;
                }
                
                if (isset($label_settings['e10']) && $label_settings['e10']) {
                    $shipment->request['order']['generalShipmentData']['product'] = 'E10';
                }
                if (isset($label_settings['e12']) && $label_settings['e12']) {
                    $shipment->request['order']['generalShipmentData']['product'] = 'E12';
                }
                if (isset($label_settings['e18']) && $label_settings['e18']) {
                    $shipment->request['order']['generalShipmentData']['product'] = 'E18';
                }
                
                $this->output["success"] = array();
                
                for ($i = 0; $i < $label_count; $i++) {
                    try {
                        $shipment->send();
                    } catch (Exception $e) {
                        Logger::addLog(
                            'Something went wrong while generating a DPD Label (' . $e->getMessage() . ')'
                            , 3
                            , null
                            , null
                            , null
                            , true
                        );
                        return false;
                    }
                    
                    if (isset($shipment->result->orderResult)) {
                        if (!is_array($shipment->result->orderResult->shipmentResponses->parcelInformation)) {
                            $parcelInformation = $shipment->result->orderResult->shipmentResponses->parcelInformation;
                            $shipment->result->orderResult->shipmentResponses->parcelInformation = array($parcelInformation);
                        }
                        foreach($shipment->result->orderResult->shipmentResponses->parcelInformation as $key => $parcelInformation) {
                            $this->createPDF($shipment, $key);
                            
                            $parcel_label_number = $parcelInformation->parcelLabelNumber;
                            $date = date("Y-m-d H:i:s");
                            
                            $services = array(
                                'cod' => $label_settings['cod']
                                ,'comp' => $label_settings['comp']
                                ,'e10' => $label_settings['e10']
                                ,'e12' => $label_settings['e12']
                                ,'e18' => $label_settings['e18']
                                ,'dps' => $label_settings['dps']
                                ,'predict' => $label_settings['predict']
                                ,'sat' => $label_settings['sat']
                            );
                            
                            Db::getInstance()->insert(
                                'dpdcarrier_label',
                                array(
                                    'id_order' => $order->id
                                    ,'parcel_number' => (string)$parcel_label_number
                                    ,'date' => $date
                                    ,'weight' => $label_settings['weight']
                                    ,'length' => $label_settings['length']
                                    ,'height' => $label_settings['height']
                                    ,'depth' => $label_settings['depth']
                                    ,'value' => $label_settings['value']
                                    ,'id_location' => $label_settings['dps'] ? $label_settings['id_location'] : ''
                                    ,'services' => serialize($services)
                                )
                            );
                            
                            $this->output["success"][] = array(
                                'parcel_number' => $parcel_label_number
                                ,'date' => $date
                                ,'weight' => $label_settings['weight']
                                ,'length' => $label_settings['length']
                                ,'height' => $label_settings['height']
                                ,'depth' => $label_settings['depth']
                                ,'value' => $label_settings['value']
                                ,'id_location' => $label_settings['dps'] ? $label_settings['id_location'] : ''
                                ,'services' => $services
                            );
                        }
                    }
                }
                
                if ($order_carrier->tracking_number == '') {
                    $order_carrier->tracking_number = $order->reference;
                    $order_carrier->save();
                }
                
            }
        }
    }
    
    private function createPDF($shipment, $key)
    {
        if (isset($shipment->result->orderResult->shipmentResponses)
          && isset($shipment->result->orderResult->shipmentResponses->parcelInformation)
          && isset($shipment->result->orderResult->shipmentResponses->parcelInformation[$key]->parcelLabelNumber)) {
            
            $parcel_label_number = $shipment->result->orderResult->shipmentResponses->parcelInformation[$key]->parcelLabelNumber;
            if (!($new_pdf = fopen($this->module->download_location . DS . $parcel_label_number . '.pdf', 'w'))) {
                Logger::addLog(
                    'The new PDF (DPD Label) file could not be created on the file system'
                    , 3
                    , null
                    , null
                    , null
                    , true
                );
                return false;
            }
            if (!fwrite($new_pdf, $shipment->result->orderResult->parcellabelsPDF)) {
                Logger::addLog(
                    'The new PDF (DPD Label) file could not be written to file system'
                    , 3
                    , null
                    , null
                    , null
                    , true
                );
                return false;
            }
            if ($new_pdf) {
                fclose($new_pdf);
            }
            
            return true;
        }
        
        return false;
    }
    
    private function getNotificationData($recipient, $language)
    {
        if ($recipient->email) {
            return array(
                'channel' => '1'
                ,'value' => $recipient->email
                ,'language' => $language
            );
        } elseif ($recipient->phone_mobile) {
            return array(
                'channel' => '3'
                ,'value' => $recipient->phone_mobile
                ,'language' => $language
            );
        }
        return array(
            'channel' => '1'
            ,'value' =>'no@no.noo'
            ,'language' => 'EN'
        );
    }
    
    private function downloadLabels()
    {
        if (!Tools::getIsset('selected_labels') || count(Tools::getValue('selected_labels')) == 0) {
            $this->output["validation"]["id_order"] = "No labels selected";
        }
        $range = Tools::getValue('selected_labels');
        
        if (count($range) > 0) {
            // Yeah ... I need to find another PDF merger...
            @ini_set('display_errors', 'off');
            include_once(
                _PS_MODULE_DIR_ . '/' . $this->module->name . DS . 'lib' . DS . 'PDFMerger' . DS . 'PDFMerger.php'
            );
            
            
            $pdf = new PDFMerger;
            @ini_set('display_errors', 'on');
            foreach ($range as $label_number) {
                $file = $this->module->download_location . DS . $label_number . '.pdf';
                if (file_exists($file)) {
                    $pdf->addPDF($file, 'all');
                } else {
                    Logger::addLog('Label ' . $label_number . ' not found on file system.', 2, null, null, null, true);
                }
            }
            
            // TODO: FLUSH PHP BUFFER
            $pdf->merge('browser', 'labels.pdf');
        } else {
            $this->output['warning']['no-labels'] = "No labels selected";
        }
    }
    
    private function infoLabels()
    {
        if (!Tools::getIsset('id_order') || Tools::getValue('id_order') =='') {
            $this->output["validation"]["id_order"] = "Couldn't determine the order you are in.";
        }
        
        if (count($this->output["validation"]) == 0) {
            $id_order = Tools::getValue('id_order');
            
            $query = new DbQuery();
            $query->select('*')
                ->from('dpdcarrier_label')
                ->where('id_order = ' . $id_order);
            
            $data = Db::getInstance()->query($query);
            $result = array();
            if ($data) {
                foreach($data as $key => $row) {
                    $result[$key] = $row;
                    $result[$key]['services'] = unserialize($row['services']);
                }
                $this->output["success"] = $result;
            }
        }
    }
}
