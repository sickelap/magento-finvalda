<?php
require_once(dirname(__FILE__) . "/../inc/properties.php");
require_once(dirname(__FILE__) . "/../plugins/inc/magmi_datasource.php");

class Magmi_DataPumpFactory
{
    static protected $_factoryprops = null;

    static function getDataPumpInstance($pumptype)
    {
        if (self::$_factoryprops == null) {
            self::$_factoryprops = new Properties();
            self::$_factoryprops->load(dirname(__FILE__) . DS . "pumpfactory.ini");
        }
        $pumpinfo = self::$_factoryprops->get("DATAPUMPS", $pumptype, "");
        $arr = explode("::", $pumpinfo);
        $pumpinst = NULL;
        if (count($arr) == 2) {
            $pumpfile = $arr[0];
            $pumpclass = $arr[1];

            try {
                require_once(dirname(__FILE__) . DS . "$pumpfile.php");
                $pumpinst = new $pumpclass();
            } catch (Exception $e) {
                $pumpinst = null;
            }
        } else {
            echo "Invalid Pump Type";
        }
        return $pumpinst;
    }
}