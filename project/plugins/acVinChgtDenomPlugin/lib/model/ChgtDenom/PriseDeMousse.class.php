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
        if ($lotOrig->volume < $lot->volume * 0.2) {
            $lotOrig->volume = 0;
        }
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

        $lots[1]->initial_type = PriseDeMousseClient::TYPE_MODEL;
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

    public function getAllPieces() {
        $lot = $this->getLotOrigine();
        $libelle = 'Prise de mousse';

        // $libelle .= ($this->isTotal())? '' : ' partiel';
        $libelle .= ' n° '.$this->numero_archive.' -';
        $libelle .= ' lot de '.$this->origine_produit_libelle.' '.$this->origine_millesime;
        $libelle .= ' - logement '.$this->origine_numero_logement_operateur.' ';
        $libelle .= ($this->isPapier())? ' (Papier)' : ' (Télédéclaration)';
        return (!$this->getValidation())? array() : array(array(
            'identifiant' => $this->getIdentifiant(),
            'date_depot' => preg_replace('/T.*/', '', $this->validation),
            'libelle' => $libelle,
            'mime' => Piece::MIME_PDF,
            'visibilite' => 1,
            'source' => null
            ));
        return array();
    }

    public static function getUrlVisualisationPiece($id, $admin = false) {
        return sfContext::getInstance()->getRouting()->generate('prisedemousse_visualisation', array('id' => $id));
    }
}
