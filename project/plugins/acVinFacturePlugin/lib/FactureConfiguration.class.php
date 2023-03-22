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
        $this->app_coordonnees_bancaire = sfConfig::get('app_facture_coordonnees_bancaire', array());

    }

    public function isActive() {

        return in_array('facturation', sfConfig::get('sf_enabled_modules'));
    }

    public function isLigneUnique() {
      return isset($this->configuration['ligneUnique']) && boolval($this->configuration['ligneUnique']);
    }

    public function isLigneDetailWithTitle() {
      return isset($this->configuration['ligneDetailWithTitle']) && boolval($this->configuration['ligneDetailWithTitle']);
    }

    public function hasEcheances() {
      return isset($this->configuration['echeances']) && $this->configuration['echeances'];
    }

    public function getEcheancesArray() {
      return $this->configuration['echeances'];
    }

    public function getUniqueTemplateFactureName(){
      if(!isset($this->configuration['uniq_template_prefix']) || !$this->configuration['uniq_template_prefix']){
        return null;
      }
      return $this->configuration['uniq_template_prefix']."-%s";
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

    public function deprecatedNumeroFactureIsId(){

        return sfConfig::get('sf_app') == 'nantes';
    }

    public function getNumeroFormat(){
      if(!isset($this->configuration['numero_format']) || !$this->configuration['numero_format']){
        return "";
      }
      return $this->configuration['numero_format'];
    }

    public function getModaliteDePaiement()
    {
        return (isset($this->configuration['modalite_paiement']) && $this->configuration['modalite_paiement']) ? $this->configuration['modalite_paiement'] : '';
    }

    public function getExercice() {

        return ($this->configuration['exercice']) ?: '';
    }

    public function isListeDernierExercice() {

        return boolval($this->configuration['liste_dernier_exercice']);
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

    public function hasDonotSaveExportFacture() {
        return
                isset($this->configuration['export_donotsave']) &&
                ($this->configuration['export_donotsave'])
              ;
    }

    public function getExportType() {
        if (!isset($this->configuration['export_type'])) {
            return null;
        }
        return $this->configuration['export_type'];
    }

    public function getCodeJournalFacture() {
        if (!isset($this->configuration['codejournal_facture'])) {
            return "VE00";
        }
        return $this->configuration['codejournal_facture'];
    }

    public function getCodeJournalPaiement() {
        if (!isset($this->configuration['codejournal_paiement'])) {
            return "5200";
        }
        return $this->configuration['codejournal_paiement'];

    }

    public function getCompteTVANormal() {
        if (!isset($this->configuration['comptetva_normal'])) {
            return "445710";
        }
        return $this->configuration['comptetva_normal'];
    }

    public function getCompteTVASuperReduit() {
        if (!isset($this->configuration['comptetva_superreduit'])) {
            return "445711";
        }
        return $this->configuration['comptetva_superreduit'];
    }

    public function getNumeroCompteBanquePaiement() {
        if (!isset($this->configuration['numerocompte_banquepaiement'])) {
            return '511150';
        }
        return $this->configuration['numerocompte_banquepaiement'];
    }

    public function displayTypesDocumentOnMassive() {
        if(!isset($this->configuration['display_types_document_on_massive'])) {

            return false;
        }

        return $this->configuration['display_types_document_on_massive'];
    }

    public function getTypesDocumentFacturant() {
        if(!isset($this->configuration['types_document_facturant'])) {

            return ["TOUS", "DRev", "DR", "SV11", "SV12", "Degustation", "ChgtDenom", "Conditionnement"];
        }

        return $this->configuration['types_document_facturant'];
    }

}
