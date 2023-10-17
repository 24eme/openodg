<?php

class ParcellaireSecurity extends DocumentSecurity implements SecurityInterface {

    protected $doc;
    protected $user;

    public static function getInstance($user, $doc) {

        return new ParcellaireSecurity($user, $doc);
    }

    public function isAuthorized($droits) {
        if(!is_array($droits)) {
            $droits = array($droits);
        }

        if ($this->user->isAdminODG() && RegionConfiguration::getInstance()->hasOdgProduits()) {
            $e = $this->doc->getEtablissementObject();
            $hab = HabilitationClient::getInstance()->findPreviousByIdentifiantAndDate($e->identifiant, date('Y-m-d'));
            foreach($hab->getProduits() as $produit) {
                if (RegionConfiguration::getInstance()->isHashProduitInRegion(Organisme::getCurrentRegion(), $produit->getProduitHash())) {
                    return true;
                }
            }
            return false;
        }

        $authorized = parent::isAuthorized($droits);

        if(!$authorized) {

            return false;
        }

        return true;
    }

}