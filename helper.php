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

class DpdHelper
{
    const moduleName = 'dpdcarrier';
    public static function getLabelLocation()
    {
        return _PS_DOWNLOAD_DIR_ . 'dpd';
    }
    
    /**
     *
     */
    public static function installControllers($list)
    {
        $failed = array();
        foreach ($list as $name => $userReadableName) {
            $tab = new Tab();
            $tab->active = 1;
            $tab->class_name = $name;
            // Hide the tab from the menu.
            $tab->id_parent = -1;
            $tab->module = self::moduleName;
            
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
    
    public static function uninstallControllers($list)
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
    
    public static function installTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->name = array();
        $tab->class_name = 'AdminDpdLabels';

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'DPD Shipping List';
        }

        $tab->id_parent = (int)Tab::getIdFromClassName('AdminShipping');
        $tab->module = self::moduleName;

        return $tab->add();
    }
    
    public static function uninstallTab()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminDpdLabels');

        if ($id_tab) {
            $tab = new Tab($id_tab);
            return(!$tab->delete());
        }
    }
    
    public static function installDB()
    {
        $file = dirname(__FILE__) .'/install/install.sql';
        $handle = fopen($file, "r");
        $query = fread($handle, filesize($file));
        fclose($handle);
        
        $query = preg_replace('/_PREFIX_/', _DB_PREFIX_, $query);
        
        return Db::getInstance()->execute($query);
    }
    
    public static function uninstallDB()
    {
        
    }
    
    public static function initCarriers()
    {
        self::loadDis();
        
        $weight_multiplier = self::getWeightMultiplier();
        $dimension_multiplier = self::getDimensionMultiplier();
        $languages = Language::getLanguages(true);
        $shipping_services = new DisServices();
        $default = $shipping_services->default;
        
        // Return false if no services defined.
        if (!isset($shipping_services->services)) {
            return false;
        }
        
        $context = Context::getContext();
        
        $country_iso =  $context->country->iso_code;
        $language_iso = $context->language->iso_code;
        
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
            $carrier->external_module_name = self::moduleName;
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
              
            Configuration::updateValue(self::generateVariableName($service->name . ' id'), (int)($carrier->id));
        }
        return true;
    }
    
    public static function removeCarriers()
    {
        self::loadDis();
        $shipping_services = new DisServices();
        
        $warnings = array();
        
        foreach ($shipping_services->services as $service) {
            $carrier_var_name = self::generateVariableName($service->name . ' id');
            $carrier = new Carrier(Configuration::get($carrier_var_name));
            
            if (!$carrier->delete() || !Configuration::deleteByName($carrier_var_name)) {
                $warnings = "Could not delete carrier " . $service->name;
            }
        }
        
        return count($warnings) == 0;
    }
    
    public static function loadDis()
    {
        $files = preg_grep('/index\.php$/', glob(dirname(__FILE__) . DS . 'lib' . DS . 'DIS' . DS . 'classes' . DS . '*.php'), PREG_GREP_INVERT);
        
        foreach ($files as $filename) {
            require_once($filename);
        }
    }
    
    public static function loadFPDx()
    {
        require_once(dirname(__FILE__) . DS . 'lib'. DS .'PDF'. DS .'FPDF'. DS .'fpdf.php');
        require_once(dirname(__FILE__) . DS . 'lib'. DS .'PDF'. DS .'FPDI'. DS .'fpdi.php');
    }
    
    
    public static function loadShippingListTemplate()
    {
        require_once(dirname(__FILE__) . DS .'classes'. DS .'pdf'. DS .'HTMLTemplateDpdShippingList.php');
    }
    
    public static function getPDFTemplate($template_name)
    {
        return dirname(__FILE__) . DS .'pdf'. DS . $template_name .'.tpl';
    }
    
    public static function getSenderAddress()
    {
        $result = new StdClass();
        $result->company = Configuration::get('PS_SHOP_NAME');
        $result->address1 = Configuration::get('PS_SHOP_ADDR1');
        $result->iso_A2 = Country::getIsoById(Configuration::get('PS_SHOP_COUNTRY_ID'));
        $result->postcode = Configuration::get('PS_SHOP_CODE');
        $result->city = Configuration::get('PS_SHOP_CITY');
        
        return $result;
    }
    
    public static function copyArray($source)
    {
        $result = array();
        
        foreach($source as $key => $item){
            $result[$key] = (is_array($item) ? self::copyArray($item) : $item);
        }
        
        return $result;
    }
    
    public static function generateDefaultLabel($order)
    {
        $order_carrier = new OrderCarrier($order->getIdOrderCarrier());
        $cart = new Cart($order->id_cart);
        $shop_info = self::getParcelShopInfo($cart);
        
        $label_settings = self::getInitialOrderSettings($order);
        $label_settings['count'] = 1;
        $label_settings['weight'] = $order_carrier->weight;
        $label_settings['length'] = 0;
        $label_settings['height'] = 0;
        $label_settings['depth'] = 0;
        $label_settings['id_location'] = $shop_info['id_location'];
        $label_settings['value'] = $order->total_paid;
        
        self::generateLabels($order, $label_settings);
    }
    
    public static function generateReturnLabel($orderReturn)
    {
        $order = new Order($orderReturn->id_order);
        $order_carrier = new OrderCarrier($order->getIdOrderCarrier());
        $prefix = Configuration::get('PS_RETURN_PREFIX', Context::getContext()->language->id);
        $return_ref = sprintf('%1$s%2$06d', $prefix, $orderReturn->id);
        
        $label_settings['return_ref'] = $return_ref;
        $label_settings['id_order_return'] = $orderReturn->id_order;
        $label_settings['count'] = 1;
        $label_settings['weight'] = 0;
        $label_settings['length'] = 0;
        $label_settings['height'] = 0;
        $label_settings['depth'] = 0;
        
        return self::generateLabels($order, $label_settings);
    }
    
    public static function generateLabels($order, $label_settings)
    {
        if (self::isDpdOrder($order) || isset($label_settings['return_ref'])) {
            $current_carrier = new Carrier($order->id_carrier);
            $order_carrier = new OrderCarrier($order->getIdOrderCarrier());
            
            $disLogin = self::getLogin();
            
            if ($disLogin) {
                self::loadDis();
                
                $sender_address = self::getSenderAddress();
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
                
                if (isset($label_settings['return_ref']) && (string)$label_settings['return_ref'] != '') {
                    $shipment->request['order']['parcels']['customerReferenceNumber2'] = $label_settings['return_ref']; //TODO: Add substring to cut off.
                    $shipment->request['order']['parcels']['returns'] = true;
                }
                
                if (isset($label_settings['weight']) && (float)$label_settings['weight'] != 0) {
                    $shipment->request['order']['parcels']['weight'] = (int)($label_settings['weight'] * (100 / self::getWeightMultiplier()));
                }
                
                if (isset($label_settings['length']) && $label_settings['length'] != 0
                    && isset($label_settings['height']) && $label_settings['height'] != 0
                    && isset($label_settings['depth']) && $label_settings['depth'] != 0) {
                    
                    $multiplier = self::getDimensionMultiplier();
                    
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
                                $shipment->request['order']['parcels'][] = self::copyArray($parcel_data);
                            }
                            
                            $label_count = 1;
                        }
                }
                
                $notification_lang_ISO = Language::getIsoById($order->id_lang);
                $notification = self::getNotificationData($recipient_customer, $notification_lang_ISO);
                
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
                
                $output = array();
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
                            // TODO: check if the pdf is written!
                            self::createPDF($shipment, $key);
                            
                            $parcel_label_number = $parcelInformation->parcelLabelNumber;
                            $date = date("Y-m-d H:i:s");
                            
                            if(isset($label_settings['return_ref']) && $label_settings['return_ref'] != '') {
                                self::createJPG((string)$parcel_label_number);
                                
                                Db::getInstance()->insert(
                                    'dpdcarrier_return',
                                    array(
                                        'id_order_return' => $label_settings['id_order_return']
                                        ,'parcel_number' => (string)$parcel_label_number
                                        ,'date' => $date
                                    )
                                );
                                
                                $output[] = array(
                                    'parcel_number' => $parcel_label_number
                                );
                                
                            } else {
                            
                                $services = array(
                                    'cod' => isset($label_settings['cod']) && $label_settings['cod']
                                    ,'comp' => isset($label_settings['comp']) && $label_settings['comp']
                                    ,'e10' => isset($label_settings['e10']) && $label_settings['e10']
                                    ,'e12' => isset($label_settings['e12']) && $label_settings['e12']
                                    ,'e18' => isset($label_settings['e18']) && $label_settings['e18']
                                    ,'dps' => isset($label_settings['dps']) && $label_settings['dps']
                                    ,'predict' => isset($label_settings['predict']) && $label_settings['predict']
                                    ,'sat' => isset($label_settings['sat']) && $label_settings['sat']
                                );
                                
                                Db::getInstance()->insert(
                                    'dpdcarrier_label',
                                    array(
                                        'id_order' => $order->id
                                        ,'parcel_number' => (string)$parcel_label_number
                                        ,'date' => $date
                                        ,'weight' => isset($label_settings['weight']) ? $label_settings['weight'] : 0
                                        ,'length' => isset($label_settings['length']) ? $label_settings['length'] : 0
                                        ,'height' => isset($label_settings['height']) ? $label_settings['height'] : 0
                                        ,'depth' => isset($label_settings['depth']) ? $label_settings['depth'] : 0
                                        ,'value' => isset($label_settings['value']) ? $label_settings['value'] : 0
                                        ,'id_location' => isset($label_settings['dps']) && $label_settings['dps'] ? $label_settings['id_location'] : ''
                                        ,'services' => serialize($services)
                                        ,'return' => isset($label_settings['return_ref']) && $label_settings['return_ref'] != ''
                                    )
                                );
                                
                                $output[] = array(
                                    'parcel_number' => $parcel_label_number
                                    ,'date' => $date
                                    ,'weight' => isset($label_settings['weight']) ? $label_settings['weight'] : 0
                                    ,'length' => isset($label_settings['length']) ? $label_settings['length'] : 0
                                    ,'height' => isset($label_settings['height']) ? $label_settings['height'] : 0
                                    ,'depth' => isset($label_settings['depth']) ? $label_settings['depth'] : 0
                                    ,'value' => isset($label_settings['value']) ? $label_settings['value'] : 0
                                    ,'id_location' => isset($label_settings['dps']) && $label_settings['dps'] ? $label_settings['id_location'] : ''
                                    ,'services' => $services
                                );
                            }
                        }
                    }
                }
                
                if ($order_carrier->tracking_number == '') {
                    $order_carrier->tracking_number = $order->reference;
                    $order_carrier->save();
                }
                
                return $output;
                
            }
        }
    }
    
    public static function createPDF($shipment, $key)
    {
        if (isset($shipment->result->orderResult->shipmentResponses)
          && isset($shipment->result->orderResult->shipmentResponses->parcelInformation)
          && isset($shipment->result->orderResult->shipmentResponses->parcelInformation[$key]->parcelLabelNumber)) {
            
            $parcel_label_number = $shipment->result->orderResult->shipmentResponses->parcelInformation[$key]->parcelLabelNumber;
            if (!($new_pdf = fopen(self::getLabelLocation() . DS . $parcel_label_number . '.pdf', 'w'))) {
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
    
    public static function createJPG($parcel_number)
    {
        $im = new imagick();
        $im->setResolution(320, 320);
        $im->readImage(self::getLabelLocation() . DS . $parcel_number .'.pdf[0]');
        $im->rotateImage(new ImagickPixel('#00000000'), -90);
        $im->setImageFormat('jpg');
        
        if (!($new_jpg = fopen(self::getLabelLocation() . DS . $parcel_number . '.jpg', 'w'))) {
            Logger::addLog(
                'The new JPG (DPD Label) file could not be created on the file system'
                , 3
                , null
                , null
                , null
                , true
            );
            return false;
        }
        if (!fwrite($new_jpg, $im)) {
            Logger::addLog(
                'The new JPG (DPD Label) file could not be written to file system'
                , 3
                , null
                , null
                , null
                , true
            );
            return false;
        }
        if ($new_jpg) {
            fclose($new_jpg);
        }
            
        return true;
        
        
    }
    
    public static function getNotificationData($recipient, $language)
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
        
        // Return default data to trigger predict/B2C servicecode.
        return array(
            'channel' => '1'
            ,'value' =>'no@no.noo'
            ,'language' => 'EN'
        );
    }
    
    public static function downloadLabels($range, $prepend = null, $append = null)
    {
        if (count($range) > 0) {
            self::loadFPDx();
            
            $size = 'A4';
            
            $pdf = new FPDI();
            
            foreach ($range as $count => $label_number) {
                $file = self::getLabelLocation() . DS . $label_number . '.pdf';
                if (file_exists($file)) {
                    $pageCount = $pdf->setSourceFile($file);
                    $tplIdx = $pdf->importPage(1);
                    
                    if( $size == 'A6' 
                        || ($size == 'A4' && ($count % 4) == 0)) {
                            $pdf->addPage('P', $size);
                    }
                    $binCount = str_pad(decbin($count+1),2,0, STR_PAD_LEFT);
                    $firstBit = (int)substr($binCount, -1);
                    $secondBit = (int)substr($binCount, -2, 1);
                    
                    
                    $verticalPos = !($firstBit xor $secondBit);
                    $pdf->useTemplate($tplIdx, !$firstBit * 105, $verticalPos * 148);
                } else {
                    Logger::addLog('Label ' . $label_number . ' not found on file system.', 2, null, null, null, true);
                }
            }
            $pdf->Output();
        } else {
            return false;
        }
    }
    
    public static function getOrderLabelInfo($order)
    {
        $query = new DbQuery();
        $query->select('*')
            ->from('dpdcarrier_label')
            ->where('id_order = ' . $order->id);
        
        $data = Db::getInstance()->query($query)->fetchAll(PDO::FETCH_ASSOC);
        $result = array();
        if ($data) {
            foreach($data as $key => $row) {
                $result[$key] = $row;
                $result[$key]['services'] = unserialize($row['services']);
            }
            return $result;
        }
    }
    
    public static function getLabelInfo($range)
    {
        $list = '(';
        foreach($range as $key => $label_number) {
            if ($key != 0) {
                $list .= ',';
            }
            $list .= '\'' . $label_number . '\''; 
        }
        $list .= ')';
        $query = new DbQuery();
        
        $query->select('*')
            ->from('dpdcarrier_label', 'psdl')
            ->leftJoin('orders', 'pso', 'psdl.id_order = pso.id_order')
            ->leftJoin('address', 'psa', 'pso.id_address_delivery = psa.id_address')
            ->where('parcel_number IN ' . $list);
        
        $data = Db::getInstance()->query($query)->fetchAll(PDO::FETCH_ASSOC);
        
        $result = array();
        if ($data) {
            foreach($data as $key => $row) {
                $result[$key] = $row;
                
                $country_iso = Country::getIsoById($row['id_country']);
                
                $result[$key]['recipient'] = $data[$key]['firstname'] . " " . $data[$key]['lastname'];
                $result[$key]['address'] = $data[$key]['address1'] . ", " . $country_iso .'-' . $data[$key]['postcode'] . " " . $data[$key]['city'];
                
                $services = unserialize($row['services']);
                $service_output = "";
                foreach($services as $name => $bool) {
                    if($bool) {
                        $service_output .= " " . $name;
                    }
                }
                $result[$key]['services'] = $service_output;
            }
            return $result;
        }
    }
    
    public static function getWeightMultiplier()
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
    
    public static function getDimensionMultiplier()
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
    
    public static function getLogin()
    {
        self::loadDis();
        
        $delisId = Configuration::get(DpdHelper::generateVariableName('delisid'));
        $delisPw = Configuration::get(DpdHelper::generateVariableName('password'));

        $url = Configuration::get(DpdHelper::generateVariableName('live_server')) == 1 ?
            'https://public-dis.dpd.nl/Services/' :
            'https://public-dis-stage.dpd.nl/Services/';
        
        return new DisLogin($delisId, $delisPw, $url);
    }
    
    public static function generateVariableName($input)
    {
        return Tools::strtoupper(self::moduleName . '_' . str_replace(" ", "_", $input));
    }
    
    public static function getParcelShopInfo($cart)
    {
        $query = new DbQuery();
        $query->select('*')->from('dpdcarrier_pickup')->where('id_cart = ' . $cart->id);
        
        return Db::getInstance()->getRow($query);
    }
    
    public static function isDpdOrder($order)
    {
        $current_carrier = new Carrier($order->id_carrier);
        
        return $current_carrier->external_module_name == 'dpdcarrier';
    }
    
    private static function isCOD($order) {
        return $order->module == 'cashondelivery';
    }
    
    public static function getInitialOrderSettings($order) {
        $currenct_carrier = new Carrier($order->id_carrier);
        
        $result = array();
        
        $shipping_services = new DisServices();
        foreach ($shipping_services->services as $service) {
        
            $service_carrier_id = Configuration::get(self::generateVariableName($service->name . ' id'));
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
        $result['cod'] = self::isCOD($order);
        
        return $result;
    }
}