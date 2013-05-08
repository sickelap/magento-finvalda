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

        foreach ($order->getAllItems() as $item) {
            $children = $item->getChildrenItems();
            if (!empty($children)) {
                continue; // bundle
            }
            $itemData = $item->getData();
            $price = round($itemData['price_incl_tax'], 2);
            $qty = intval($itemData['qty_ordered']);
            $XML .= "
                <PardDokPrekeDetEil>
                    <sKodas>{$itemData['sku']}</sKodas>
                    <sPavadinimas>{$itemData['name']}</sPavadinimas>
                    <sSandelis>CENTR.</sSandelis>
                    <nKiekis>{$qty}</nKiekis>
                    <dSumaL>{$price}</dSumaL>
                    <dSumaV>{$price}</dSumaV>
                    <dPVM_Procentas>21</dPVM_Procentas>
                </PardDokPrekeDetEil>";
        }

        $shippingAmount = round($order->getShippingAmount(), 2);
        $XML .= "
            <PardDokPaslaugaDetEil>
                <sKodas>TRANSPORTAV</sKodas>
                <dSumaV>{$shippingAmount}</dSumaV>
                <dSumaL>{$shippingAmount}</dSumaL>
                <nKiekis>100</nKiekis>
                <dPVM_Procentas>21</dPVM_Procentas>
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