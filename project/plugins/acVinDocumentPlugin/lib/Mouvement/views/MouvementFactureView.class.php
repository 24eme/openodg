<?php

class MouvementFactureView extends acCouchdbView
{

    const KEY_FACTURE = 0;
    const KEY_FACTURABLE = 1;
    const KEY_ETB_ID = 2;
    const KEY_DETAIL = 3;
    const KEY_TYPE = 4;
    const KEY_DATE = 5;
    const KEY_DOC_ORIGIN = 6;
    const KEY_ORIGIN = 7;

    public static function getInstance() {

        return acCouchdbManager::getView('mouvement', 'facture');
    }

    public function getMouvementsFacturesBySociete($societe, $facturee = 0, $facturable = 1) {

        if($societe instanceof Compte) {

            return $this->getMouvementsFacturesByCompte($societe, $facturee, $facturable);
        }

	return $this->client
	  ->startkey(array($facturee,$facturable,$societe->identifiant.'00'))
	  ->endkey(array($facturee,$facturable,$societe->identifiant.'99', array()))
	  ->reduce(false)
	  ->getView($this->design, $this->view)->rows;
    }

    public function getMouvementsFacturesByCompte($compte, $facturee = 0, $facturable = 1) {
	return $this->client
	  ->startkey(array($facturee,$facturable,$compte->identifiant))
	  ->endkey(array($facturee,$facturable,$compte->identifiant, array()))
	  ->reduce(false)
	  ->getView($this->design, $this->view)->rows;
    }

    public function getMouvementsFacturesEnAttente($region = null)
    {
        $rows = array();
        foreach( $this->client->startkey([0, 1])
                            ->endkey([0, 1, []])
                            ->reduce(false)
                            ->getView($this->design, $this->view)->rows as $r) {
            if ($region && $r->value->region != $region ) {
                continue;
            }
            $rows[] = $r;
        }
        return $rows;
    }
}
