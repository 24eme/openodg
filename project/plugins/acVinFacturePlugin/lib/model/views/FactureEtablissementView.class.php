<?php

class FactureEtablissementView extends acCouchdbView
{
    const KEYS_VERSEMENT_TYPE = 0;
    const KEYS_VERSEMENT_COMPTABLE = 1;
    const KEYS_CLIENT_ID = 2;
    const KEYS_FACTURE_ID = 3;

    const VALUE_DATE_FACTURATION = 0;
    const VALUE_ORIGINES = 1;
    const VALUE_TOTAL_TTC = 2;
    const VALUE_STATUT = 3;

    const VERSEMENT_TYPE_FACTURE = "FACTURE";
    const VERSEMENT_TYPE_PAIEMENT = "PAIEMENT";
    const VERSEMENT_TYPE_SEPA = "SEPA";


    public static function getInstance() {

        return acCouchdbManager::getView('facture', 'etablissement', 'Facture');
    }


    public function getFactureNonVerseeEnCompta() {

       return acCouchdbManager::getClient()
                    ->startkey(array(self::VERSEMENT_TYPE_FACTURE, 0))
                    ->endkey(array(self::VERSEMENT_TYPE_FACTURE, 0, array()))
                    ->getView($this->design, $this->view)->rows;
    }

    public function getPaiementNonVerseeEnCompta() {

       return acCouchdbManager::getClient()
                    ->startkey(array(self::VERSEMENT_TYPE_PAIEMENT, 0))
                    ->endkey(array(self::VERSEMENT_TYPE_PAIEMENT, 0, array()))
                    ->getView($this->design, $this->view)->rows;
    }

    public function getPaiementNonExecuteSepa() {

       return acCouchdbManager::getClient()
                    ->startkey(array(self::VERSEMENT_TYPE_SEPA, 0))
                    ->endkey(array(self::VERSEMENT_TYPE_SEPA, 0, array()))
                    ->getView($this->design, $this->view)->rows;
    }
    
    public function getAllFacturesForCompta() {

       return acCouchdbManager::getClient()
                    ->startkey(array(self::VERSEMENT_TYPE_FACTURE))
                    ->endkey(array(self::VERSEMENT_TYPE_FACTURE, array()))
                    ->getView($this->design, $this->view)->rows;
    }

    public function findByEtablissement($identifiant) {
            $rows = acCouchdbManager::getClient()
                    ->startkey(array(self::VERSEMENT_TYPE_FACTURE, 0, $identifiant))
                    ->endkey(array(self::VERSEMENT_TYPE_FACTURE, 0, $identifiant, array()))
                    ->getView($this->design, $this->view)->rows;
            return array_merge($rows, acCouchdbManager::getClient()
                    ->startkey(array(self::VERSEMENT_TYPE_FACTURE, 1, $identifiant))
                    ->endkey(array(self::VERSEMENT_TYPE_FACTURE, 1, $identifiant, array()))
                    ->getView($this->design, $this->view)->rows);

    }

}
