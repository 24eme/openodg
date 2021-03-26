<?php

class MouvementFactureView extends acCouchdbView
{

    const KEYS_FACTURE = 0;
    const KEYS_FACTURABLE = 1;
    const KEYS_ETB_ID = 2;
    const KEYS_DETAIL = 3;
    const KEYS_TYPE = 4;

    public static function getInstance() {

        return acCouchdbManager::getView('mouvement', 'facture');
    }


    public function getMouvementsFacturesBySociete($societe,$facturee, $facturable) {
	return $this->client
	  ->startkey(array($facturee,$facturable,$societe->identifiant.'00'))
	  ->endkey(array($facturee,$facturable,$societe->identifiant.'99', array()))
	  ->reduce(false)
	  ->getView($this->design, $this->view)->rows;
    }

}
