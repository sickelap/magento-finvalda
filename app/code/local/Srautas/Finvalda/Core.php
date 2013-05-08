<?php

/**
 * @author Genadijus Paleckis <genadijus.paleckis@srautas.lt>
 */

class Srautas_Finvalda_Core {

    private $debug_enabled = FALSE;
    private $proxy = FALSE;

    public function __construct()
    {
        $wsdlURL = Mage::getStoreConfig('finvalda/connection/url');
        $username = Mage::getStoreConfig('finvalda/connection/username');
        $password = Mage::getStoreConfig('finvalda/connection/password');
        $this->debug_enabled = Mage::getStoreConfig('finvalda/advanced/debug');

        $options = array('trace' => 1);
        if (Mage::getStoreConfig('finvalda/connection/use_proxy')) {
            $options['proxy_host'] = Mage::getStoreConfig('finvalda/connection/proxy_host');
            $options['proxy_port'] = Mage::getStoreConfig('finvalda/connection/proxy_port');
        }

        $this->proxy = new SoapClient($wsdlURL, $options);
        $authHeader = new SOAPHeader(
            'http://www.fvs.lt/webservices',
            'AuthHeader',
            array(
                'UserName' => $username,
                'Password' => $password
            )
        );
        $this->proxy->__setSoapHeaders($authHeader);

        if (!$this->proxy) {
            $this->debug('Connection error');
        }
    }

    /**
     * @param string $message
     */
    public function debug($message = 'MESSAGE_NOT_SET')
    {
        if ($this->debug_enabled) {
            Mage::log(sprintf("%s: %s", __CLASS__, $message));
        }
    }

    /**
     * @return bool|object
     */
    public function getProxy()
    {
        return $this->proxy;
    }
}

?>
