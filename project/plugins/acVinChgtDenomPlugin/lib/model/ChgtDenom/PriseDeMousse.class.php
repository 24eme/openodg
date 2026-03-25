<?php

class PriseDeMousse extends ChgtDenom  {

    public function __construct() {
        parent::__construct();
        $this->type = PriseDeMousseClient::TYPE_MODEL;
    }

    public function getDocumentType() {
        return PriseDeMousseClient::TYPE_MODEL;
    }

    public function generateLots() {

        $this->prepareGenerateLots();

        $lots = array();
        $lot = $this->prepareInitialLot();
        if (!$lot) {
            return ;
        }

        $lotOrig = $this->generateLotOrigine($lot);
        $lots[] = $lotOrig;

        $lot->produit_libelle = $this->changement_produit_libelle;
        $lot->produit_hash = $this->changement_produit_hash;
        $lot->cepages = $this->changement_cepages;
        $lot->statut = null;
        $lot->affectable = null;
        $lot->numero_archive = null;
        $lot->campagne = $this->campagne;
        $lot->numero_archive = null;
        $lot->unique_id = null;
        $lot->document_ordre = '01';
        $lot->specificite = $this->changement_specificite;
        $lot->affectable = $this->changement_affectable;

        $this->updateCepageCoherencyWithVolume($lot);
        $lots[] = $lot;

        $this->registerLots($lots);

    }

    public function getLotsWithPseudoDeclassement() {
        $lots_res = array();
        foreach($this->lots as $lot) {
            if ($lot->volume > 0) {
                $lots_res[] = $lot;
            }
        }
        return $lots_res;
    }

}
