<?php

/**
 * @author Genadijus Paleckis <genadijus.paleckis@srautas.lt>
 */

class Srautas_Finvalda_Observer_Customer {

    /*
     * Sukuriame klienta FVS sistemoje
     */
    public function updateCustomer($event)
    {
        $customer = $event->getCustomer()->getData();

        $ID = sprintf("WEBC%010d", $customer['entity_id']);
        $customerName = $customer['firstname'].' '.$customer['lastname'];
        if (strlen($customerName) > 100) {
            $customerName = substr($customerName, 0, 100);
        }

        $customerEmail = $customer['email'];
        if (!preg_match('/^[a-z][a-z0-9\.\-]+\@[a-z0-9\.\-]+$/i', $customerEmail)) {
            $customerEmail = sprintf("%s@babycenter.lt", strtolower($customerName));
        }
        if (strlen($customerEmail) > 50) {
            $customerEmail = substr($customerEmail, 0, 50);
        }

        $customerAddressXML = '';
        $customerPhoneXML = '';
        $customerFaxXML = '';

        /** @var $fvs Srautas_Finvalda_Webservice */
        $fvs = Mage::getSingleton('finvalda/webservice');

        $fvs->GetKlientas(array(
            'sKliKod' => $ID,
            'writeSchema' => FALSE
        ));
        $klientas = $fvs->getResult();

        if ($klientas) {
            if ((string)$klientas->sPavadinimas != $customerName || (string)$klientas->sEMail != $customerEmail) {
                $XML  = "<?xml version=\"1.0\"?>";
                $XML .= "<Fvs.Klientas>";
                $XML .= "   <sKodas>{$ID}</sKodas>";
                $XML .= "   <sPavadinimas>{$customerName}</sPavadinimas>";
                $XML .= "   <sEMail>{$customerEmail}</sEMail>";
                $XML .= "   {$customerAddressXML}";
                $XML .= "   {$customerPhoneXML}";
                $XML .= "   {$customerFaxXML}";
                $XML .= "</Fvs.Klientas>";
                $fvs->EditItem(array(
                    'ItemClassName' => 'Fvs.Klientas',
                    'sItemCode' => $ID,
                    'xmlString' => $XML
                ));
            }
        } else {
            $XML  = "<?xml version=\"1.0\"?>";
            $XML .= "   <Fvs.Klientas>";
            $XML .= "   <sFvsImportoParametras>PIRMAS</sFvsImportoParametras>";
            $XML .= "   <sKodas>{$ID}</sKodas>";
            $XML .= "   <sPavadinimas>{$customerName}</sPavadinimas>";
            $XML .= "   <sEMail>{$customerEmail}</sEMail>";
            $XML .= "   <sRusis>LOJ KL INT</sRusis>"; // use config?
            $XML .= "   {$customerAddressXML}";
            $XML .= "   {$customerPhoneXML}";
            $XML .= "   {$customerFaxXML}";
            $XML .= "</Fvs.Klientas>";
            $fvs->InsertNewItem(array(
                'ItemClassName' => 'Fvs.Klientas',
                'xmlString' => $XML
            ));
        }

        return $this;
    }
}
