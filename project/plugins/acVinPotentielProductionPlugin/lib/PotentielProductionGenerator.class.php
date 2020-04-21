<?php
abstract class PotentielProductionGenerator
{
    protected $etablissement;
    protected $parcellaire;
    
    public function __construct($identifiant_or_etablissement) 
    {
        if (is_object($identifiant_or_etablissement)) {
            if ($identifiant_or_etablissement instanceof Etablissement) {
                $this->etablissement = $identifiant_or_etablissement;
            }
        } else {
            $this->etablissement = EtablissementClient::getInstance()->findByIdentifiant(EtablissementClient::getInstance()->getIdentifiant($identifiant_or_etablissement));
        }
        if (!$this->etablissement) {
            throw new Exception("Etablissement non identifié");
        }
        if (!class_exists('ParcellaireClient')) {
            throw new sfException("Le calcul du potentiel de production necessite le parcellaire dans le projet");
        }
        $this->parcellaire = ParcellaireClient::getInstance()->getLast($this->etablissement->identifiant);
        if (!$this->parcellaire) {
            throw new sfException("Pas de parcellaire trouvé pour ".$this->etablissement->identifiant);
        }
    }
    
    public function infos() {
        return  ' - Etablissement : '.$this->etablissement->_id."\n - Dernier parcellaire : ".$this->parcellaire->_id."\n";
    }
}