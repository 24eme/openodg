<?php

class MouvementLotHistoryView extends acCouchdbView
{
    const KEY_DECLARANT_IDENTIFIANT = 0;
    const KEY_NUMERO_DOSSIER = 1;
    const KEY_NUMERO_ARCHIVE = 2;
    const KEY_DOC_ORDRE = 3;
    const KEY_STATUT = 4;
    const KEY_ORIGINE_DOCUMENT_ID = 5;

    const VALUE_LOT = 0;

    public static function getInstance()
    {
        return acCouchdbManager::getView('mouvement', 'lotHistory');
    }

    public function getMouvements($declarant, $dossier, $archive)
    {
        return $this->client
                    ->startkey([$declarant, $dossier, $archive])
                    ->endkey([$declarant, $dossier, $archive, []])
                    ->getView($this->design, $this->view);
    }

    public static function generateLotByMvt($mvt)
    {
        $lot = new stdClass();
        $lot->date = $mvt->date;
        $lot->id_document = $mvt->origine_document_id;
        $lot->numero_dossier = $mvt->numero_dossier;
        $lot->numero_archive = $mvt->numero_archive;
        $lot->numero_cuve = $mvt->numero_cuve;
        $lot->millesime = $mvt->millesime;
        $lot->volume = $mvt->volume;
        $lot->destination_type = $mvt->destination_type;
        $lot->destination_date = $mvt->destination_date;
        $lot->produit_hash = $mvt->produit_hash;
        $lot->produit_libelle = $mvt->produit_libelle;
        $lot->declarant_nom = $mvt->declarant_nom;
        $lot->declarant_identifiant = $mvt->declarant_identifiant;
        $lot->origine_mouvement = $mvt->origine_mouvement;
        $lot->details = $mvt->details;
        $lot->elevage = (isset($mvt->elevage))? $mvt->elevage : null;
        $lot->statut = $mvt->statut;
        $lot->specificite = (isset($mvt->specificite))? $mvt->specificite : null;
        if(isset($mvt->centilisation)) {
            $lot->centilisation = isset($mvt->centilisation) ? $mvt->centilisation : null;
        }
        if (isset($mvt->nombre_degustation)) {
            $lot->nombre_degustation = $mvt->nombre_degustation;
        }
        return $lot;
    }
}
