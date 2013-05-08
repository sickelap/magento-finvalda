<?php
/**
 * @author Genadijus Paleckis <genadijus.paleckis@srautas.lt>
 */

/**
 * @method GetEinamiejiLikuciaiXml(array)
 * @method GetEinamiejiLikuciaiExtXml(array)
 * @method GetKlientuRusisPozymiusXml(array)
 * @method GetKlientusSetXml(array)
 * @method GetNeapmoketiKliDokXml(array)
 * @method GetPaslaugosSetXml(array)
 * @method GetPaslauguRusisPozymiusXml(array)
 * @method GetPrekesSetXml(array)
 * @method GetPrekiuRusisPozymiusXml(array)
 * @method GetPrekiuRusiuGrupesXml(array)
 * @method GetPrekiuRusiuGrupesSudetiXml(array)
 * @method GetSandeliusXml(array)
 * @method GetKliPrekPasNuolPapKainXml(array)
 * @method GetKliRusPrekPasNuolPapKainXml(array)
 * @method GetKliPrekPasRusNuolPapKainXml(array)
 * @method GetKliRusPrekPasRusNuolPapKainXml(array)
 * @method GetMokesciaiXml(array)
 * @method GetKlientuPaslauguNuolXml(array)
 * @method GetKlientuPaslauguPapKainasXml(array)
 * @method GetKlientuPaslauguRusNuolXml(array)
 * @method GetKlientuPaslauguRusPapKainasXml(array)
 * @method GetKlientuPrekiuNuolXml(array)
 * @method GetKlientuPrekiuPapKainasXml(array)
 * @method GetKlientuPrekiuRusNuolXml(array)
 * @method GetKlientuPrekiuRusPapKainasXml(array)
 * @method GetKlientuRusPaslauguNuolXml(array)
 * @method GetKlientuRusPaslauguPapKainasXml(array)
 * @method GetKlientuRusPaslauguRusNuolXml(array)
 * @method GetKlientuRusPaslauguRusPapKainasXml(array)
 * @method GetKlientuRusPrekiuNuolXml(array)
 * @method GetKlientuRusPrekiuPapKainasXml(array)
 * @method GetKlientuRusPrekiuRusNuolXml(array)
 * @method GetKlientuRusPrekiuRusPapKainasXml(array)
 * @method GetFvsUser(array)
 * @method GetKlientas(array)
 * @method GetKlientus(array)
 * @method GetPaslauga(array)
 * @method GetPaslaugos(array)
 * @method GetPreke(array)
 * @method GetPrekes(array)
 * @method GetPrekesSandelyje(array)
 * @method GetPrekiuRusiesSudetiXml(array)
 * @method GetPaslauguRusiesSudetiXml(array)
 * @method GetKlientusRusiesSudetiXml(array)
 * @method GetAtsiskaitymoTermXml(array)
 * @method InsertNewItem(array)
 * @method EditItem(array)
 * @method InsertNewOperation(array)
 * @method DeleteOperation(array)
 */

/**
 * Class Srautas_Finvalda_Webservice
 */
class Srautas_Finvalda_Webservice extends Srautas_Finvalda_Core {

    /**
     * FVS WebService metodu sarasas su reiksmemis pagal nutylejima
     *
     * TODO: sudelioti defaultinius parametrus visiems webserviso metodams
     */
    private $methods = array(
        /**
         * 'READ' metodai
         */
        'GetEinamiejiLikuciaiXml' => array(),
        'GetEinamiejiLikuciaiExtXml' => array(),
        'GetKlientuRusisPozymiusXml' => array(),
        'GetKlientusSetXml' => array(),
        'GetNeapmoketiKliDokXml' => array(),
        'GetPaslaugosSetXml' => array(),
        'GetPaslauguRusisPozymiusXml' => array(),
        'GetPrekesSetXml' => array(),
        'GetPrekiuRusisPozymiusXml' => array(),
        'GetPrekiuRusiuGrupesXml' => array(),
        'GetPrekiuRusiuGrupesSudetiXml' => array(),
        'GetSandeliusXml' => array(),
        'GetKliPrekPasNuolPapKainXml' => array(),
        'GetKliRusPrekPasNuolPapKainXml' => array(),
        'GetKliPrekPasRusNuolPapKainXml' => array(),
        'GetKliRusPrekPasRusNuolPapKainXml' => array(),
        'GetMokesciaiXml' => array(),
        'GetKlientuPaslauguNuolXml' => array(),
        'GetKlientuPaslauguPapKainasXml' => array(),
        'GetKlientuPaslauguRusNuolXml' => array(),
        'GetKlientuPaslauguRusPapKainasXml' => array(),
        'GetKlientuPrekiuNuolXml' => array(),
        'GetKlientuPrekiuPapKainasXml' => array(),
        'GetKlientuPrekiuRusNuolXml' => array(),
        'GetKlientuPrekiuRusPapKainasXml' => array(),
        'GetKlientuRusPaslauguNuolXml' => array(),
        'GetKlientuRusPaslauguPapKainasXml' => array(),
        'GetKlientuRusPaslauguRusNuolXml' => array(),
        'GetKlientuRusPaslauguRusPapKainasXml' => array(),
        'GetKlientuRusPrekiuNuolXml' => array(),
        'GetKlientuRusPrekiuPapKainasXml' => array(),
        'GetKlientuRusPrekiuRusNuolXml' => array(),
        'GetKlientuRusPrekiuRusPapKainasXml' => array(),
        'GetFvsUser' => array(),
        'GetKlientas' => array(),
        'GetKlientus' => array(),
        'GetPaslauga' => array(),
        'GetPaslaugos' => array(),
        'GetPreke' => array(),
        'GetPrekes' => array(),
        'GetPrekesSandelyje' => array(),
        'GetPrekiuRusiesSudetiXml' => array(),
        'GetPaslauguRusiesSudetiXml' => array(),
        'GetKlientusRusiesSudetiXml' => array(),
        'GetAtsiskaitymoTermXml' => array(),

        /**
         * 'WRITE' metodai
         */
        'InsertNewItem' => array(),
        'EditItem' => array(),
        'InsertNewOperation' => array(),
        'DeleteOperation' => array()
    );

    private $result;
    private $error;

    /**
     * Kvieciame Finvaldos webserviso metoda.
     * Graziname TRUE jei operacija pavyko arba FALSE jei ivyko klaida. Klaidos atveju nustatome "error" savybe.
     * Jei klaidos nebuvo, talpiname rezultata "result" savybeje.
     *
     * @param $method
     * @param $arguments
     * @return boolean
     */
    public function __call($method, $arguments)
    {
        if (!in_array($method, array_keys($this->methods))) {
            $this->debug('Nezinomas metodas: ' . $method);
            return array();
        }

        if (!$proxy = $this->getProxy()) {
            $this->debug('Negaliu prisijungti prie FVS');
            return array();
        }

        /**
         * TODO: Tikrinti ar nurodyti parametrai leidziami webserviso metode (kai bus sudeti defaultiniai parametrai).
         */
        $result = $proxy->$method($arguments[0]);
        $resultProperty = $method . 'Result';
        if (isset($result->$resultProperty) && $result->$resultProperty == 'Success') {
            $this->result = $result;
            $this->error = NULL;

            return TRUE;
        }

        $this->result = NULL;
        $this->error = $result->sError;

        return FALSE;
    }

    /**
     * For use with cache
     *
     * @param $content
     * @return $this
     */
    public function setResultXml($content)
    {
        if (!is_object($this->result)) {
            $this->result = new stdClass();
        }
        $this->result->Xml = $content;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResultRaw()
    {
        return $this->result;
    }

    /**
     * @return string
     */
    public function getResultXml()
    {
        if ($this->result == NULL) {
            return $this->error;
        }

        if (isset($this->result->Xml) && $this->result->Xml != '') {
            return $this->result->Xml;
        }

        return 'no data';
    }

    /**
     * @return SimpleXMLElement
     */
    public function getResult()
    {
        if ($this->result == NULL) {
            return new SimpleXMLElement('');
        }

        if (isset($this->result->Xml) && $this->result->Xml != '') {
            return simplexml_load_string($this->result->Xml);
        }

        return new SimpleXMLElement('');
    }

    /**
     * @return array
     */
    public function getResultArray()
    {
        if ($this->result == NULL) {
            return array();
        }

        if (isset($this->result->Xml) && $this->result->Xml != '') {
            return $this->object2array(simplexml_load_string($this->result->Xml));
        }

        return array();
    }

    /**
     * utele objekto konvertavimui i paprasta masyva
     *
     * @param $arrObjData       mixed   Input data
     * @param $arrSkipIndices   array   Indices to skip
     * @return                  array
     */
    function object2array($arrObjData, $arrSkipIndices = array())
    {
        $arrData = array();

        if (is_object($arrObjData)) {
            $arrObjData = get_object_vars($arrObjData);
        }

        if (is_array($arrObjData)) {
            foreach ($arrObjData as $index => $value) {
                if (is_object($value) || is_array($value)) {
                    $value = call_user_func_array(array($this, __FUNCTION__), array($value, $arrSkipIndices));
                }
                if (in_array($index, $arrSkipIndices)) {
                    continue;
                }
                $arrData[$index] = (empty($value)) ? NULL : $value;
            }
        }

        return $arrData;
    }
}