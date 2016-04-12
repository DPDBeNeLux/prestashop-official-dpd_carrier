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
 
class PdfOrderReturnController extends PdfOrderReturnControllerCore
{
    public function display()
    {
        Module::getInstanceByName('DpdCarrier')->loadHelper();
        
        DpdHelper::loadFPDx();
        
        $pdf = new PDF($this->orderReturn, PDF::TEMPLATE_ORDER_RETURN, $this->context->smarty);
        $page1 = $pdf->render(false);
        
        $temp = tmpfile();
        fwrite($temp, $page1);
        fseek($temp,0);
        $meta_data = stream_get_meta_data($temp);
        $filename = $meta_data["uri"];
        
        $pdfi = new FPDI();
        
        $pageCount = $pdfi->setSourceFile($filename);
        for($i = 1; $i < $pageCount+1; $i++) {
            $tplIdx = $pdfi->importPage($i, '/MediaBox');
        }

        $pdfi->addPage();
        $pdfi->useTemplate($tplIdx, 0, 0);
        
        fclose($temp);
        
        $label = DpdHelper::getLabelLocation() . DS . '05308300001456' . '.pdf';
        
        $pageCount = $pdfi->setSourceFile($label);
        $tplIdx = $pdfi->importPage(1, '/MediaBox');

        $pdfi->addPage();
        $pdfi->useTemplate($tplIdx, 0, 0);

        $pdfi->Output();
    }
}
