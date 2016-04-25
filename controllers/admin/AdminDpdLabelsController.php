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
    
    protected function processBulkPrintLabels()
    {
        if (is_array($this->boxes) && !empty($this->boxes)) {
            if (ob_get_level() && ob_get_length() > 0) {
                ob_clean();
            }
            DpdHelper::downloadLabels($this->boxes);
        }
    }
    
    protected function processBulkPrintList()
    {
        if (is_array($this->boxes) && !empty($this->boxes)) {
            $data = DpdHelper::getLabelInfo($this->boxes);
            DpdHelper::loadShippingListTemplate();
            DpdHelper::setLabelShipped($this->boxes);
            
            $pdf = new PDF(array($data), 'DpdShippingList', Context::getContext()->smarty, 'L');
            $pdf->render();
            die;
        }
    }
    
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'dpdcarrier_label';
        $this->className = 'AdminDpdLabelsController';
        $this->lang = false;
        $this->export = true;
        $this->row_hover = false;
        
        $this->fields_list = array(
            'reference' => array('title' => $this->l('Order Ref'), 'class' => 'fixed-width-xs')
            ,'parcel_number' => array(
                'title' => $this->l('Parcel Number')
                ,'class' => 'fixed-width-sm'
            )
            ,'weight' => array('title' => $this->l('Weight'), 'class' => 'fixed-width-xs')
            ,'length' => array('title' => $this->l('Length'), 'class' => 'fixed-width-xs')
            ,'height' => array('title' => $this->l('Height'), 'class' => 'fixed-width-xs')
            ,'depth' => array('title' => $this->l('Depth'), 'class' => 'fixed-width-xs')
            ,'value' => array(
                'title' => $this->l('Value')
                ,'class' => 'fixed-width-xs'
                ,'type' => 'price'
                ,'currency' => true
            )
            ,'id_location' => array('title' => $this->l('Location'), 'class' => 'fixed-width-xs')
            ,'address' => array('title' => $this->l('Address'))
            ,'services' => array(
                'title' => $this->l('Services')
                ,'class' => 'fixed-width-xs'
                ,'search' => false
                ,'orderby' => false
            )
        );
        
        $this->bulk_actions = array(
            'printLabels' => array(
                'text' => $this->l('Print labels'),
                'icon' => 'icon-print'
            )
            ,'printList' => array(
                'text' => $this->l('Print list'),
                'icon' => 'icon-print'
            )
            // ,'delete' => array(
                // 'text' => $this->l('Delete selected'),
                // 'confirm' => $this->l('Delete selected items?'),
                // 'icon' => 'icon-trash'
            // )
        );
        
        parent::__construct();
        
        $this->table = 'dpdcarrier_label';
        $this->_select = 'pso.*, CONCAT_WS(\' \', psa.lastname, psa.address1, psa.postcode, psa.city) as address';
        $this->_join = 'JOIN '._DB_PREFIX_.'orders AS pso ON a.id_order = pso.id_order JOIN '._DB_PREFIX_.
            'address AS psa ON pso.id_address_delivery = psa.id_address';
        $this->_where = 'AND shipped <> 1';
        $this->_defaultOrderBy = 'a.id_order';
        
        $this->identifier = 'parcel_number';
        
        $this->module->loadHelper();
    }
    
    public function getList(
        $id_lang,
        $order_by = null,
        $order_way = null,
        $start = 0,
        $limit = null,
        $id_lang_shop = false
    ) {
        parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);
        
        if ($this->_list) {
            foreach ($this->_list as $key => $row) {
                $services = unserialize($row['services']);
                $service_output = "";
                foreach ($services as $name => $bool) {
                    if ($bool) {
                        $service_output .= " " . $name;
                    }
                }
                
                $this->_list[$key]['services'] = $service_output;
            }
        }
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
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }
    
    public function displayAjax()
    {
        if (Tools::getIsset('action')) {
            switch(Tools::getValue('action')) {
                case 'generate':
                    $this->generateLabels();
                    break;
                case 'download':
                    $this->downloadLabels();
                    break;
                case 'info':
                    $this->infoLabels();
                    break;
                default:
                    Tools::redirect(__PS_BASE_URI__);
                    die;
            }
        } else {
            Tools::redirect(__PS_BASE_URI__);
            die;
        }
        
        echo Tools::jsonEncode($this->output);
        die;
    }
    
    private function generateLabels()
    {
        if (!Tools::getIsset('label_count') || (int)Tools::getValue('label_count') <= 0) {
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
            $label_settings = array();
            
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
            
            $this->output['success'] = DpdHelper::generateLabels($order, $label_settings);
        }
    }
    
    private function downloadLabels()
    {
        if (!Tools::getIsset('selected_labels') || count(Tools::getValue('selected_labels')) == 0) {
            $this->output["validation"]["id_order"] = "No labels selected";
        }
        $range = Tools::getValue('selected_labels');
        
        if (count($range) > 0 && $range) {
            DpdHelper::downloadLabels($range);
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
            $order = new Order($id_order);
            
            $this->output["success"] = DpdHelper::getOrderLabelInfo($order);
        }
    }
}
