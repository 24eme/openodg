<?php

class DRevConfiguration {

    private static $_instance = null;
    protected $configuration;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new DRevConfiguration();
        }
        return self::$_instance;
    }

    public function load() {
        $this->configuration = sfConfig::get('drev_configuration_drev', array());
    }

    public function __construct() {
        if(!sfConfig::has('drev_configuration_drev')) {
			throw new sfException("La configuration pour les drev n'a pas été défini pour cette application");
		}

        $this->load();
    }

    public function getSpecificites(){
        if($this->hasSpecificiteLot()){
            return $this->configuration['specificites'];
        }
    }

    public function hasPrelevements() {

        return isset($this->configuration['prelevements']) && boolval($this->configuration['prelevements']);
    }

    public function hasImportDRWithMentionsComplementaire() {

        return isset($this->configuration['import_with_mentions_complementaire']) && boolval($this->configuration['import_with_mentions_complementaire']);
    }

    public function hasMentionsCompletaire() {

        return isset($this->configuration['mentions_complementaire']) && boolval($this->configuration['mentions_complementaire']);
    }

    public function hasDenominationAuto() {

      return isset($this->configuration['denomination_auto']) && boolval($this->configuration['denomination_auto']);
    }

    public function hasExploitationSave() {
      return isset($this->configuration['exploitation_save']) && boolval($this->configuration['exploitation_save']);
    }

    public function hasHabilitationINAO() {
        return isset($this->configuration['habilitation_inao']) && ($this->configuration['habilitation_inao']);
    }

    public function hasValidationOdgAuto(){
      return isset($this->configuration['validation_odg']) && $this->configuration['validation_odg'] == 'auto';
    }

    public function hasValidationOdgAdmin(){
      return isset($this->configuration['validation_odg']) && $this->configuration['validation_odg'] == 'admin';
    }

    public function hasValidationOdgRegion(){
      return isset($this->configuration['validation_odg']) && $this->configuration['validation_odg'] == 'region';
    }

    public function hasValidationOdgAutoOrRegion(){
      return $this->hasValidationOdgAuto() || $this->hasValidationOdgRegion();
    }

    public function hasValidationOdgAdminOrRegion(){
      return $this->hasValidationOdgAdmin() || $this->hasValidationOdgRegion();
    }

    public function hasValidationOdgAdminOrAuto(){
      return $this->hasValidationOdgAdmin() || $this->hasValidationOdgAuto();
    }

    public function hasCgu(){
      return isset($this->configuration['cgu']) && boolval($this->configuration['cgu']);
    }

    public function hasEtapeSuperficie() {
        return isset($this->configuration['etape_superficie']) && boolval($this->configuration['etape_superficie']);
    }


    public function isDrDouaneRequired() {
        return isset($this->configuration['dr_douane_required']) && boolval($this->configuration['dr_douane_required']);
    }

    public function getDateBeginDouane($annee = null) {
        if (!$annee) {
            $annee = date('Y');
        }
        return $annee.'1031';
    }

    public function hasDegustation() {
        return isset($this->configuration['degustation']) && boolval($this->configuration['degustation']);
    }

    public function hasPDFUniqueRegion() {
        return isset($this->configuration['pdf_unique_region']) && boolval($this->configuration['pdf_unique_region']);
    }
    public function hasSpecificiteLot(){
      return isset($this->configuration['specificite_lot']) && boolval($this->configuration['specificite_lot']);
    }

    public function hasEngagementsPdf(){
      return isset($this->configuration['engagement_pdf']) && boolval($this->configuration['engagement_pdf']);
    }


    public function hasLogementAdresse() {
        return isset($this->configuration['logement_adresse']) && boolval($this->configuration['logement_adresse']);
    }

    public function isRevendicationParLots() {

        return ConfigurationClient::getCurrent()->declaration->isRevendicationParLots();
    }

    public function isSendMailToOperateur() {

        return $this->configuration['send_email_operateur'];
    }

}
