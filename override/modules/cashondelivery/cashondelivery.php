<?php

class CashOnDeliveryOverride extends CashOnDelivery
{
    public function hasProductDownload($cart) {
        return parent::hasProductDownload($cart) || !$this->isCODAllowed($cart);
    }
    
    private function isCODAllowed($cart) {
        $id_carrier = $cart->id_carrier;
        $cart_carrier = new Carrier($id_carrier);
        
        $special_carrier_ids = array(
            Configuration::get('DPDCARRIER_PICKUP_ID')
            ,Configuration::get('DPDCARRIER_GUARANTEE_ID')
            ,Configuration::get('DPDCARRIER_EXPRESS_10_ID')
            ,Configuration::get('DPDCARRIER_EXPRESS_10_ID')
            ,Configuration::get('DPDCARRIER_EXPRESS_12_ID')
        );
        
        foreach ($special_carrier_ids as $special_carrier_id) {
            $special_carrier = new Carrier($special_carrier_id);
        
            if ($special_carrier->id_reference == $cart_carrier->id_reference) {
                return false;
            }
        }
        
        return true;
        
    }
    
}
