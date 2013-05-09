<?php

/**
 * @author Genadijus Paleckis <genadijus.paleckis@srautas.lt>
 */

class Srautas_Finvalda_Observer_Document {

    public function createReservationDocument($event)
    {
        $order = new Mage_Sales_Model_Order();
        $incrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();

        $order->loadByIncrementId($incrementId);
        $orderData = $order->getData();

        $customerID = sprintf("WEBC%010d", $order['customer_id']);
        $orderDate = date('Y-m-d H:i:s');
        $XML = "
        <TrumpasPardRezDok>
            <sKlientas>{$customerID}</sKlientas>
            <tData>{$orderDate}</tData>
            <sSerija></sSerija>
            <sDokumentas></sDokumentas>
            <sValiuta>{$orderData['order_currency_code']}</sValiuta>";

        $VAT = 21;

        foreach ($order->getAllItems() as $item) {
            $children = $item->getChildrenItems();
            if (!empty($children)) {
                continue; // bundle
            }
            $itemData = $item->getData();
            $priceVat = round($itemData['price_incl_tax'], 4);
            $priceNoVat = round($priceVat / ($VAT / 100 + 1), 4);
            $priceVatValue = round($priceVat - $priceNoVat, 4);
            $qty = intval($itemData['qty_ordered']);
            $XML .= "
                <PardDokPrekeDetEil>
                    <sKodas>{$itemData['sku']}</sKodas>
                    <sPavadinimas>{$itemData['name']}</sPavadinimas>
                    <sSandelis>CENTR.</sSandelis>
                    <nKiekis>{$qty}</nKiekis>
                    <dSumaL>{$priceNoVat}</dSumaL>
                    <dSumaV>{$priceNoVat}</dSumaV>
                    <dSumaPVMV>{$priceVatValue}</dSumaPVMV>
                    <dSumaPVML>{$priceVatValue}</dSumaPVML>
                </PardDokPrekeDetEil>";
        }

        $shippingAmountVat = round($order->getShippingAmount(), 4);
        $shippingAmountNoVat = round($shippingAmountVat / ($VAT / 100 + 1), 4);
        $shippingVatValue = round($shippingAmountVat - $shippingAmountNoVat, 4);
        $XML .= "
            <PardDokPaslaugaDetEil>
                <sKodas>TRANSPORTAV</sKodas>
                <nKiekis>100</nKiekis>
                <dSumaV>{$shippingAmountNoVat}</dSumaV>
                <dSumaL>{$shippingAmountNoVat}</dSumaL>
                <dSumaPVMV>{$shippingVatValue}</dSumaPVMV>
                <dSumaPVML>{$shippingVatValue}</dSumaPVML>
    	    </PardDokPaslaugaDetEil>
        ";
        $XML .= "</TrumpasPardRezDok>";

        /** @var $fvs Srautas_Finvalda_Webservice */
        $fvs = Mage::getSingleton('finvalda/webservice');
        $fvs->InsertNewOperation(array(
            'ItemClassName' => 'TrumpasPardRezDok',
            'sParametras' => 'PIRMAS', // Konfiguruojamas instaliuojant finvaldos webservisa
            'xmlString' => $XML
        ));

        return $this;
    }
}
