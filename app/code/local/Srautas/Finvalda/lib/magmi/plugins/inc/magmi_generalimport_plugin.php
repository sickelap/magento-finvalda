<?php
require_once(dirname(__FILE__)."/magmi_plugin.php");
abstract class Magmi_GeneralImportPlugin extends Magmi_Plugin
{
	public function beforeImport()
	{
		return true;
	}
	
	public function afterImport()
	{
		return true;
	}
	
}
