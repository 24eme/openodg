<?php
class PotentielProductionProvenceGenerator extends PotentielProductionGenerator
{
    protected $identificationParcellaire;
    
    public function __construct($identifiant_or_etablissement)
    {
        parent::__construct($identifiant_or_etablissement);
        $this->identificationParcellaire = ParcellaireAffectationClient::getInstance()->getLast($this->etablissement->identifiant);
        if (!$this->identificationParcellaire) {
            $this->identificationParcellaire = ParcellaireIntentionAffectationClient::getInstance()->getLast($this->etablissement->identifiant);
        }
    }
    public function infos() {
        $infos = "** PotentielProductionProvenceGenerator **\n\n".parent::infos();
        return  ($this->identificationParcellaire)? $infos.' - Identification parcellaire : '.$this->identificationParcellaire->_id."\n" : $infos." - Identification parcellaire : null\n";
    }
}