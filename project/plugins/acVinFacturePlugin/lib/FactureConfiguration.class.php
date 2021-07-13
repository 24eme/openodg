<?php

class FactureConfiguration {

    private static $_instance = null;
    protected $configuration;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new FactureConfiguration();
        }
        return self::$_instance;
    }

    public function __construct() {
        if(!sfConfig::has('facture_configuration_facture')) {
			throw new sfException("La configuration pour les factures n'a pas été défini pour cette application");
		}

        $this->configuration = sfConfig::get('facture_configuration_facture', array());
    }

    public function isActive() {

        return in_array('facturation', sfConfig::get('sf_enabled_modules'));
    }

    public function isAggregateLignes() {
      return isset($this->configuration['aggregateLignes']) && boolval($this->configuration['aggregateLignes']);
    }

    public function getAggregateLignesMsg() {
      if(!isset($this->configuration['ligneMsg'])){
        return "";
      }
      return $this->configuration['ligneMsg'];
    }

    public function hasEcheances() {
      return isset($this->configuration['echeances']) && $this->configuration['echeances'];
    }

    public function getEcheancesArray() {
      return $this->configuration['echeances'];
    }

    public function getUniqueTemplateFactureName($campagne = null){
      if(!$campagne || !isset($this->configuration['uniq_template_prefix']) || !$this->configuration['uniq_template_prefix']){
        return null;
      }
      return $this->configuration['uniq_template_prefix']."-".$campagne;
    }

    public function isFacturationAllEtablissements(){
      return isset($this->configuration['all_etablissements']) && $this->configuration['all_etablissements'];
    }

    public function getPrefixCodeComptable(){
      if(!isset($this->configuration['prefix_code_comptable']) || !$this->configuration['prefix_code_comptable']){
        return "";
      }
      return $this->configuration['prefix_code_comptable'];
    }

    public function hasExportSageWithTxt(){
      if(!isset($this->configuration['export_sage_width_txt']) || !$this->configuration['export_sage_width_txt']){
        return "";
      }
      return $this->configuration['export_sage_width_txt'];
    }

    public function getNumeroCampagne(){
      if(!isset($this->configuration['numero_campagne']) || !$this->configuration['numero_campagne']){
        return false;
      }
      return $this->configuration['numero_campagne'];
    }

    public function getNumeroFormat(){
      if(!isset($this->configuration['numero_format']) || !$this->configuration['numero_format']){
        return "";
      }
      return $this->configuration['numero_format'];
    }

    public function getNumeroFormatDocuments(){
      if(!isset($this->configuration['numero_format_documents']) || !$this->configuration['numero_format_documents']){
        return "";
      }
      return $this->configuration['numero_format_documents'];
    }

    public function getModaliteDePaiement()
    {
        return ($this->configuration['modalite_paiement']) ?: '';
    }

    public function getExercice() {

        return ($this->configuration['exercice']) ?: '';
    }

    public function hasPaiements(){
      return isset($this->configuration['paiements']) && $this->configuration['paiements'];
    }

    public function getDelaisPaiement() {
        if(!isset($this->configuration['delais_paiement'])) {

            return null;
        }

        return $this->configuration['delais_paiement'];
    }

    /* @Deprecated use Organisme::getInstance() */
    public function getInfos($region = null) {

        return Organisme::getInstance($region)->getInfos();
    }

    /* @Deprecated use Organisme::getInstance() */
    public function getInfo($key, $region = null) {

        return Organisme::getInstance($region)->getInfo($key);
    }
}
