<?php

/*** AVA ***/

class /*** AVA ***/ EtablissementSecurity implements SecurityInterface {

    const DECLARANT_DREV = 'DECLARANT_DREV';
    const DECLARANT_PARCELLAIRE = 'DECLARANT_PARCELLAIRE';

    protected $user;
    protected $etablissement;

    public static function getInstance($user, $etablissement) {

        return new EtablissementSecurity($user, $etablissement);
    }

    public function __construct($user, $etablissement) {
        $this->user = $user;
        $this->etablissement = $etablissement;
    }

    public function isAuthorized($droits) {
        if(!is_array($droits)) {
            $droits = array($droits);
        }

        if ($this->user->isAdminODG() && RegionConfiguration::getInstance()->hasOdgProduits()) {
            $hab = HabilitationClient::getInstance()->findPreviousByIdentifiantAndDate($this->etablissement->identifiant, date('Y-m-d'));
            foreach($hab->getProduits() as $produit) {
                if (RegionConfiguration::getInstance()->isHashProduitInRegion(Organisme::getCurrentRegion(), $produit->getProduitHash())) {
                    return true;
                }
            }
            return false;
        }
        /*** DECLARANT ***/

        if(!$this->user->isAdmin() && $this->user->getEtablissement()->_id != $this->etablissement->_id) {

            return false;
        }

        if(in_array(self::DECLARANT_DREV, $droits) && !$this->etablissement->hasFamille(EtablissementClient::FAMILLE_VINIFICATEUR) && !count(RegistreVCIClient::getInstance()->getHistory($this->etablissement->identifiant, acCouchdbClient::HYDRATE_JSON))) {

            return false;
        }

        return true;
    }

}
