<?php

class Organisme
{
    const DEFAULT_TYPE = 'facture';
    const FACTURE_TYPE = 'facture';
    const DEGUSTATION_TYPE = 'degustation';

    private static $organismes = array();
    private $region = null;
    private $type = null;

    public static function getCurrentRegion() {
        if(sfConfig::get('app_region')) {
            return strtoupper(sfConfig::get('app_region'));
        }

        if (sfContext::hasInstance() && sfContext::getInstance()->getUser()->getRegion()) {
            return strtoupper(sfContext::getInstance()->getUser()->getRegion());
        }
        return null;
    }

    public static function getOIRegion() {
        return 'OIVC';
    }

    public static function getCurrentOrganisme() {
        $region = self::getCurrentRegion();
        if ($region) {
            return $region;
        }
        return strtoupper(sfConfig::get('sf_app'));
    }

    public static function getInstance($region = null, $type = self::DEFAULT_TYPE) {
        $region = ($region) ?: self::getCurrentOrganisme();

        if (in_array($type, [self::DEGUSTATION_TYPE, self::FACTURE_TYPE]) === false) {
            $type = self::DEFAULT_TYPE;
        }

        if (!array_key_exists($region.$type, self::$organismes)) {
            self::$organismes[$region.$type] = new Organisme($region, $type);
        }
        return self::$organismes[$region.$type];
    }

    public function __construct($region, $type) {
        $this->region = $region;
        $this->type = $type;
    }

    public function getStaticInfos($region, $type) {
        if (!sfConfig::has('app_'.$type.'_emetteur') && $type != self::DEFAULT_TYPE) {
            return self::getStaticInfos($region, self::DEFAULT_TYPE);
        }
        $app = sfConfig::get('app_'.$type.'_emetteur');
        if (!isset($app[$region]) && $type != self::DEFAULT_TYPE) {
            return self::getStaticInfos($region, self::DEFAULT_TYPE);
        }
        return $app;
    }

    public function isOC()
    {
        $oc = RegionConfiguration::getInstance()->getOC();
        return Organisme::getCurrentOrganisme() === $oc;
    }

    public function getInfos() {
       $infos = (sfConfig::has('app_'.$this->type.'_emetteur'))
                   ? sfConfig::get('app_'.$this->type.'_emetteur')
                   : sfConfig::get('app_'.self::DEFAULT_TYPE.'_emetteur');

       if (!$infos || !array_key_exists($this->region, $infos)) {
           $infos = sfConfig::get('app_'.self::DEFAULT_TYPE.'_emetteur');
           return $infos;
       }

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

        return $this->getInfo('nom') ? $this->getInfo('nom') : $this->getInfo('service_facturation');
    }

    public function getNomFacturation() {

        return $this->getInfo('service_facturation') ? $this->getInfo('service_facturation') : $this->getInfo('nom');
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

    public function getEmailFacturation() {

        return ($this->getInfo('email_facturation')) ? $this->getInfo('email_facturation') : $this->getInfo('email');
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

    public function getOiMed() {

        return $this->getInfo('oimed');
    }

    public function getUrl() {

        return $this->getInfo('url');
    }

    public function getLogoPath() {
        return sfConfig::get('sf_web_dir')."/".$this->getLogoWebPath();
    }

    public function getLogoWebPath() {
        return 'images/logo_'.strtolower($this->region).'.png';
    }

    public function getLogoPdfPath() {
        return sfConfig::get('sf_web_dir')."/".$this->getLogoPdfWebPath();
    }

    public function getLogoPdfWebPath() {
        return 'images/pdf/logo_'.strtolower($this->region).'.jpg';
    }

    public function getImageSignaturePath() {

        return sfConfig::get('sf_web_dir').'/'.$this->getImageSignatureWebPath();
    }

    public function getImageSignatureWebPath() {

        return 'images/signatures/signature_'.$this->region.'.jpg';
    }

    public function getBic(){
      return $this->getInfo('bic');
    }

    public function getCreditorId(){
      return $this->getInfo('creditor_id');
    }

    public function getBanqueNom(){
      return $this->getInfo('banque_nom');
    }

    public function getBanqueAdresse(){
      return $this->getInfo('banque_adresse');
    }
}
