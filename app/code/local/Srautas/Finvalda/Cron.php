<?php

/**
 * @author Genadijus Paleckis <genadijus.paleckis@srautas.lt>
 */

require_once(__DIR__ . '/lib/magmi/plugins/inc/magmi_datasource.php');
require_once(__DIR__ . '/lib/magmi/integration/productimport_datapump.php');

/**
 * Class Srautas_Finvalda_Cron
 */
class Srautas_Finvalda_Cron {

    private $enabled;

    /** @var $catalogUpdated DateTime */
    private $catalogUpdated;

    /** @var $now DateTime */
    private $now;

    /** @var $stockUpdated DateTime */
    private $stockUpdated;

    /** @var $fvs Srautas_Finvalda_Webservice */
    private $fvs;

    /** @var $config Mage_Core_Model_Config */
    private $config;

    const RUSIS    = 0;
    const POZYMIS1 = 1;
    const POZYMIS2 = 2;
    const POZYMIS3 = 5;
    const POZYMIS4 = 9;
    const POZYMIS5 = 10;
    const POZYMIS6 = 11;

    private $_pozymiai;
    private $_likuciai;

    public function __construct()
    {
        $this->enabled = Mage::getStoreConfig('finvalda/general/enabled');
        $this->fvs = Mage::getModel('finvalda/webservice');
        $this->config = Mage::getModel('core/config');

        $catalogUpdated = Mage::getStoreConfig('finvalda/advanced/catalog_updated');
        if (!$catalogUpdated) {
            //$this->catalogUpdated = new DateTime('2013-03-05T20:28:50+00:00');
            $this->catalogUpdated = new DateTime('1970-01-01T00:00:00+00:00');
            $this->config->saveConfig('finvalda/advanced/catalog_updated', $this->catalogUpdated->format(DateTime::ATOM));
        } else {
            $this->catalogUpdated = new DateTime($catalogUpdated);
        }

        $stockUpdated = Mage::getStoreConfig('finvalda/advanced/stock_updated');
        if (!$stockUpdated) {
            //$this->stockUpdated = new DateTime('2013-03-05T20:28:50+00:00');
            $this->stockUpdated = new DateTime('1970-01-01T00:00:00+00:00');
            $this->config->saveConfig('finvalda/advanced/stock_updated', $this->stockUpdated->format(DateTime::ATOM));
        } else {
            $this->stockUpdated = new DateTime($stockUpdated);
        }

        $this->now = new DateTime('now', new DateTimeZone('Europe/Vilnius'));
    }

    public function updateCatalog()
    {
        if (!$this->enabled) return;

        $this->fvs->debug("fetching products");
        $dateTimeVar = new SoapVar($this->catalogUpdated->format(DateTime::ATOM), XSD_DATETIME, "dateTime", 'http://www.w3.org/2001/XMLSchema');
        $this->fvs->GetPrekes(array(
            'tKoregavimoData' => $dateTimeVar,
            'writeSchema' => FALSE
        ));

        /** @var $products SimpleXMLElement */
        $products = $this->fvs->getResult();

        $this->fvs->debug(sprintf("fetched %d products", $products->count()));

        $items = array();
        $groupPrices = array();
        foreach ($products->children() as $product) {
            if ($item = $this->buildSimpleProduct($product)) {
                $items[] = $item;
                if ($groupPrice = $this->buildGroupPrice($product)) {
                    $groupPrices[] = $groupPrice;
                }
            }
        }

        if (count($items) > 0) {
            $this->fvs->debug("importing products");
            $this->import($items);

            $this->fvs->debug("importing product group prices");
            $this->import($groupPrices);

            $this->config->saveConfig('finvalda/advanced/catalog_updated', $this->now->format(DateTime::ATOM));
        }
    }

    public function updateStock()
    {
        if (!$this->enabled) return;

        $this->fvs->debug("fetching stock data");

        $dateTimeVar = new SoapVar($this->stockUpdated->format(DateTime::ATOM), XSD_DATETIME, "dateTime", 'http://www.w3.org/2001/XMLSchema');
        $this->fvs->GetEinamiejiLikuciaiXml(array(
            'sSandelioKodas'    => 'CENTR.', // TODO: use config
            'tKoregavimoData'   => $dateTimeVar,
            'writeSchema'       => FALSE
        ));

        $items = array();
        /** @var $stockData SimpleXMLElement */
        $stockData = $this->fvs->getResult();

        $this->fvs->debug(sprintf("fetched stock data for %d products", $stockData->count()));

        foreach ($stockData->children() as $stockItem) {
            $this->fvs->GetPreke(array(
                'sPrekesKodas'  => (string)$stockItem->preke,
                'writeSchema'   => false,
            ));
            $product = $this->fvs->getResult();
            $qty = (int)$stockItem->kiekis_su_rezervuotom;
            if ($qty < 0) $qty = 0;
            if ($item = $this->buildSimpleProduct($product, (int)$qty)) {
                $items[] = $item;
            }
        }

        if (count($items) > 0) {
            $this->import($items, 'create', 'cataloginventory_stock');
            $this->config->saveConfig('finvalda/advanced/stock_updated', $this->now->format(DateTime::ATOM));
        }
    }

    private function import($items, $mode = 'create', $indexes = 'all')
    {
        if (count($items) > 0) {
            $this->fvs->debug(sprintf("  processing %s items", count($items)));
            $dp = new Magmi_ProductImport_DataPump();
            $dp->beginImportSession("babycenter", $mode);
            foreach ($items as $item) {
                $dp->ingest($item);
            }
            $dp->endImportSession();
            $this->fvs->debug("  reindexing");
            $this->reindex($indexes);
            $this->fvs->debug("  processing done");
        }
    }

    private function reindex($string = 'all')
    {
        /** @var $indexer Mage_Index_Model_Indexer */
        $indexer = Mage::getModel('index/indexer');

        $processes = array();

        if ($string == 'all') {
            $processes = $indexer->getProcessesCollection();
        } else {
            $codes = explode(',', $string);
            foreach ($codes as $code) {
                $process = $indexer->getProcessByCode(trim($code));
                if (!$process) {
                    $this->fvs->debug(sprintf('    (%s::%s) Warning: Unknown indexer with code ', __CLASS__, __FUNCTION__, trim($code)));
                } else {
                    $processes[] = $process;
                }
            }
        }

        /** @var $process Mage_Index_Model_Process */
        foreach ($processes as $process) {
            $this->fvs->debug(sprintf("    running indexer %s", $process->getIndexerCode()));
            $start = microtime(true);

            $process->reindexEverything();

            $elapsed = microtime(true) - $start;
            $this->fvs->debug(sprintf("    indexer %s finished in %s", $process->getIndexerCode(), $elapsed));
        }
    }

    private function getPozymiai($sku, $type)
    {
        if (!$this->_pozymiai) {
            /** @var $fvs Srautas_Finvalda_Webservice */
            $fvs = Mage::getModel('finvalda/webservice');
            $fvs->GetPrekiuRusisPozymiusXml(array(
                'writeSchema' => false
            ));
            foreach ($fvs->getResult()->children() as $entry) {
                $this->_pozymiai[(string)$entry->tipas][(string)$entry->kodas] = (string)$entry->pavadinimas;
            }
        }

        if (isset($this->_pozymiai[(string)$type]) && isset($this->_pozymiai[(string)$type][(string)$sku])) {
            return $this->_pozymiai[(string)$type][(string)$sku];
        }

        return NULL;
    }

    private function getLikuciai($sku = NULL)
    {
        if (!$this->_likuciai) {
            $cacheFile = NULL;
            $useCache = FALSE;
            $cacheLifetime = Mage::getStoreConfig('finvalda/advanced/webservice_cache_lifetime');
            if (intval($cacheLifetime) > 0) {
                $tmpDir = Mage::getBaseDir('tmp');
                $cacheFile = $tmpDir . DS . "GetEinamiejiLikuciaiXml.xml";
                if (file_exists($cacheFile) && $fp = fopen($cacheFile, "r")) {
                    $fstat = fstat($fp); fclose($fp);
                    if ($fstat['mtime'] < time() + $cacheLifetime && intval($fstat['size']) > 0) {
                        $useCache = TRUE;
                    }
                }
            }

            /** @var $fvs Srautas_Finvalda_Webservice */
            $fvs = Mage::getModel('finvalda/webservice');
            if ($useCache && $cacheFile) {
                $cacheContent = file_get_contents($cacheFile);
                $content = $fvs->setResultXml($cacheContent)->getResult();
            } else {
                /** @var $fvs Srautas_Finvalda_Webservice */
                $fvs->GetEinamiejiLikuciaiXml(array(
                    'sSandelioKodas'    => 'CENTR.', // TODO: use config
                    'writeSchema'       => false
                ));
                $content = $fvs->getResult();
                if (intval($cacheLifetime) > 0) {
                    file_put_contents($cacheFile, $fvs->getResultXml());
                }
            }

            $this->_likuciai = array();
            foreach ($content->children() as $item) {
                $qty = (int)$item->kiekis_su_rezervuotom;
                if ($qty < 0) $qty = 0;
                $this->_likuciai[(string)$item->preke] = $qty;
            }
        }

        return ($sku && isset($this->_likuciai[(string)$sku])) ?  $this->_likuciai[(string)$sku] : 0;
    }

    private function buildSimpleProduct($item, $qty = null)
    {
        $cat = array();
        if (!empty($item->sPozymis1)) {
            $cat[] = $this->getPozymiai($item->sPozymis1, self::POZYMIS1); // . '::1::0::1';
        } else {
            return array(); // jei pozymis 1 nenurodytas, skipinam preke visai
        }
        if (!empty($item->sPozymis2)) {
            $cat[] = $this->getPozymiai($item->sPozymis2, self::POZYMIS2); // . '::1::0::1';
        }
        if (!empty($item->sPozymis3)) {
            $cat[] = $this->getPozymiai($item->sPozymis3, self::POZYMIS3); // . '::1::0::1';
        }

        /**
         * Defaults
         */
        if ($qty === null) {
            $qty = $this->getLikuciai($item->sKodas);
        }
        $status = "1"; //Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
        $visibility = "4"; //Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH;
        $stock_status = ($qty > 0) ? "1" : "0"; //? Mage_CatalogInventory_Model_Stock::STOCK_IN_STOCK : Mage_CatalogInventory_Model_Stock::STOCK_OUT_OF_STOCK;
        $backorders = "0"; //Mage_CatalogInventory_Model_Stock::BACKORDERS_NO; // No backorders
        $disable_purchase = "0";

        // TODO: Pakeisti sPozymis6 (test) -> sPozymis5 (live)
        $option = $item->sPozymis5;

        if ($option == "NOWEB") {
            $status = "2"; //Mage_Catalog_Model_Product_Status::STATUS_DISABLED;
        }

        if ($option == "HIDDEN") {
            $visibility = "1"; //Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE;
        }

        if ($option == "INSTOCK") {
            $stock_status = "1"; //Mage_CatalogInventory_Model_Stock::STOCK_IN_STOCK;
            $backorders = "2"; //Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NOTIFY;
        }

        if ($option == "PURCHDIS") {
            $disable_purchase = "1";
        }

        if ($option == "STPURCHDIS") {
            $disable_purchase = "1";
            $stock_status = "1"; //Mage_CatalogInventory_Model_Stock::STOCK_IN_STOCK;
            $backorders = "2"; //Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NOTIFY;
        }

        $attr_set = (!$this->getPozymiai($item->sPozymis4, self::POZYMIS4)) ? "Default" : $this->getPozymiai($item->sPozymis4, self::POZYMIS4);

        /**
         * Senas prekes tiesiog slepiame
         */
        if ($item->nAktyvi != 1) {
            $visibility = "1"; //Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE;
            $status = "2"; //Mage_Catalog_Model_Product_Status::STATUS_DISABLED;
            $attr_set = "Default";
        }

        $this->createAttributeSet($attr_set);

        $sku = preg_replace('/[\"\']/', '', $item->sKodas);
        $name = preg_replace('/[\"\']/', '', $item->sPavadinimas);
        $description = $name;
        $short_description = $name;

        $product = array(
            'websites'                  => 'base',
            'type'                      => 'simple',
            'sku'                       => $sku,
            'name'                      => $name,
            'url_key'                   => preg_replace('/[\"\']/', '', str_replace('\\', '-', $item->sKodas)),
            'price'                     => (float)$item->dKaina4,
            'weight'                    => (float)$item->dNeto,
            'status'                    => (string)$status,
            'qty'                       => (string)$qty,
            'is_in_stock'               => (string)$stock_status,
            'use_config_backorders'     => "0", // custom backorders config for product
            'backorders'                => $backorders,
            'visibility'                => $visibility,
            'tax_class_id'              => 'None',
            'attribute_set'             => $attr_set,
            'categories'                => implode('/', $cat),
            'manufacturer'              => $this->getPozymiai($item->sRusis, self::RUSIS),
            'country_of_manufacture'    => (string)$item->sKilmesSalis,
            'news_from_date'            => date("Y-m-d", strtotime($item->tKurimoData)),
            'news_to_date'              => date("Y-m-d", strtotime('+1 months', strtotime($item->tKurimoData))),

            'purchasedisabled'          => $disable_purchase,
        );

        if ((string)$item->sInformacija != "keep") {
            $product['description'] = $description;
            $product['short_description'] = $short_description;
        }

        return $product;
    }

    private function buildGroupPrice($item)
    {
        $groupPrice = array(
            "sku"       => preg_replace('/[\"\']/', '', $item->sKodas)
        );

        if ((double)$item->dKaina1) {
            $groupPrice['group_price:Grupe1'] = (double)$item->dKaina1;
        }
        if ((double)$item->dKaina2) {
            $groupPrice['group_price:Grupe2'] = (double)$item->dKaina2;
        }
        if ((double)$item->dKaina3) {
            $groupPrice['group_price:Grupe3'] = (double)$item->dKaina3;
        }
        if ((double)$item->dKaina4) {
            $groupPrice['group_price:Grupe4'] = (double)$item->dKaina4;
        }
        if ((double)$item->dKaina5) {
            $groupPrice['group_price:Grupe5'] = (double)$item->dKaina5;
        }
        if ((double)$item->dKaina6) {
            $groupPrice['group_price:Grupe6'] = (double)$item->dKaina6;
        }

        return (count(array_keys($groupPrice)) > 1) ? $groupPrice : null;
    }

    private function createAttributeSet($setName)
    {
        $setName = trim($setName);

        if ($setName == '') {
            return false;
        }

        /** @var $attrSetModel Mage_Eav_Model_Entity_Attribute_Set */
        $attrSetModel = Mage::getModel('eav/entity_attribute_set');
        $entityTypeID = Mage::getModel('catalog/product')->getResource()->getTypeId();

        $attrSet = $attrSetModel
            ->getCollection()
            ->setEntityTypeFilter($entityTypeID)
            ->addFieldToFilter('attribute_set_name', $setName)
            ->getFirstItem()
            ->getAttributeSetId()
        ;

        if ($attrSet) {
            return false;
        }

        $attrSetModel->setEntityTypeId($entityTypeID);
        $attrSetModel->setAttributeSetName($setName);
        $attrSetModel->validate();

        try {
            $attrSetModel->save();
        } catch (Exception $ex) {
            return false;
        }

        $attrSetModel->initFromSkeleton($entityTypeID);

        try {
            $attrSetModel->save();
        } catch (Exception $ex) {
            return false;
        }

        if (($id = $attrSetModel->getId()) == false) {
            return false;
        }
    }

}