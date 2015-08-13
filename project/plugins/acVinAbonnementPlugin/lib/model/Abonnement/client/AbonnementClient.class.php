<?php

class AbonnementClient extends acCouchdbClient implements FacturableClient {
    
    const TARIF_MEMBRE = 'MEMBRE';
    const TARIF_PLEIN = 'PLEIN';
    const TARIF_GRATUIT = 'GRATUIT';
    const TARIF_ETRANGER = 'ETRANGER';

    public static function getInstance()
    {
        
        return acCouchdbManager::getClient("Abonnement");
    }

    public function findFacturable($identifiant, $periode) {

        return $this->find(sprintf("ABONNEMENT-%s-%s", $identifiant, $periode));
    }

    public function findOrCreateDoc($identifiant, $date_debut, $date_fin) {
        $doc = $this->find(sprintf("ABONNEMENT-%s-%s-%s", $identifiant, str_replace("-", "", $date_debut), str_replace("-", "", $date_fin)));

        if(!$doc) {
            $doc = new Abonnement();
            $doc->date_debut = $date_debut;
            $doc->date_fin = $date_fin;
            $doc->identifiant = $identifiant;
            $doc->constructId();
        }

        return $doc;
    }

    public function getAbonnementsByCompte($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $ids = $this->startkey(sprintf("ABONNEMENT-%s-%s-%s", $identifiant, "00000000", "00000000"))
                    ->endkey(sprintf("ABONNEMENT-%s-%s-%s", $identifiant, "99999999", "99999999"))
                    ->execute(acCouchdbClient::HYDRATE_ON_DEMAND)->getIds();

        $factures = array();            

        foreach($ids as $id) {
            $factures[$id] = FactureClient::getInstance()->find($id, $hydrate);
        }

        krsort($factures);

        return $factures;
    }
}
