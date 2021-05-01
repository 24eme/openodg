<?php

class Organisme
{
    private static $organismes = array();
    private $region = null;

    public static function getCurrentRegion() {

        return strtoupper(sfConfig::get('sf_app'));
    }

    public static function getInstance($region = null) {
        if(is_null($region)) {
            $region = self::getCurrentRegion();
        }
        if (!array_key_exists($region, self::$organismes)) {
            self::$organismes[$region] = new Organisme($region);
        }
        return self::$organismes[$region];
    }

    public function __construct($region) {
        $this->region = $region;
    }

    public function getInfos() {
        $infos = sfConfig::get('app_facture_emetteur');

        if (!array_key_exists($this->region, $infos)) {
            throw new sfException(sprintf('Config %s not found in app.yml', $this->region));
        }

        return $infos[$this->region];
    }

    public function getInfo($key) {
        $infos = $this->getInfos();

        if(!isset($infos[$key])) {

            return null;
        }

        return $infos[$key];
    }

    public function getRegion() {

        return $this->region;
    }

    public function getNom() {

        return $this->getInfo('service_facturation');
    }

    public function getAdresse() {

        return $this->getInfo('adresse');
    }

    public function getCodePostal() {

        return $this->getInfo('code_postal');
    }

    public function getCommune() {

        return $this->getInfo('ville');
    }

    public function getTelephone() {

        return $this->getInfo('telephone');
    }

    public function getEmail() {

        return $this->getInfo('email');
    }

    public function getResponsable() {

        return $this->getInfo('responsable');
    }

    public function getIban() {

        return $this->getInfo('iban');
    }

    public function getNoTvaIntracommunautaire() {

        return $this->getInfo('tva_intracom');
    }

    public function getSiret() {

        return $this->getInfo('siret');
    }

    public function getOi() {

        return $this->getInfo('oi');
    }

    public function getLogoPdfPath() {
        return sfConfig::get('sf_web_dir')."/".$this->getLogoPdfWebPath();
    }

    public function getLogoPdfWebPath() {
        return 'images/pdf/logo_'.strtolower($this->region).'.jpg';
    }

}