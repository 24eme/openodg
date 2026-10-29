<?php

class MouvementLotView extends acCouchdbView
{
    const KEY_STATUT = 0;
    const KEY_DECLARANT_IDENTIFIANT = 1;
    const KEY_CAMPAGNE = 2;
    const KEY_LOT_UNIQUE_ID = 3;
    const KEY_DOCUMENT_ORDRE = 4;
    const KEY_DOC_ID = 5;
    const KEY_DETAIL = 6;
    const KEY_REGION = 7;

    public static function getInstance() {

        return acCouchdbManager::getView('mouvement', 'lot');
    }

    public function getByStatut($statut) {

        return $this->client->startkey(array($statut))
                            ->endkey(array($statut, array()))
                            ->getView($this->design, $this->view);
    }


    public function getByIdentifiant($identifiant, $statut = null) {

        return $this->client->startkey(array($statut, $identifiant))
                            ->endkey(array($statut, $identifiant, array()))
                            ->getView($this->design, $this->view);
    }


    public function getMouvementsByStatutIdentifiantAndUniqueId($statut, $declarant_identifiant, $uniqueId) {
        $mouvements = $this->client
                           ->startkey([
                               $statut,
                               $declarant_identifiant,
                               substr($uniqueId, 0, 9),
                               $uniqueId,
                           ])
                           ->endkey([
                               $statut,
                               $declarant_identifiant,
                               substr($uniqueId, 0, 9),
                               $uniqueId,
                               array()
                           ])
                           ->getView($this->design, $this->view);
        if (!count($mouvements->rows)) {
            return null;
        }
        $mvt = [];
        foreach($mouvements->rows as $r) {
            $mvt[] = $r->value;
        }
        return $mvt;
    }

    public function getNombreAffecteSourceAvantMoi($lot)
    {
        $history = LotsClient::getInstance()->getHistory($lot->declarant_identifiant, $lot->unique_id);
        $nb = 0;
        foreach($history as $h) {
            if ($h->value->document_ordre >= $lot->document_ordre) {
                continue;
            }
            if (!in_array($h->value->statut, [Lot::STATUT_CONFORME, Lot::STATUT_NONCONFORME])) {
                continue;
            }
            if ($h->value->statut == Lot::STATUT_CONFORME) {
                $nb = 0;
                continue;
            }
            $nb++;
         }
         return $nb + 1;
     }


    public function find($identifiant, $query, $first = true) {
        $mvts = $this->getMouvements($identifiant, $query);
        if(!$first) {

            return $mvts;
        }
        if (!count($mvts)) {
            return null;
        }

        return $mvts[0];
    }

    public function getMouvements($identifiant, $query) {
        $statuts = null;
        if(isset($query['statut'])) {
            $statuts = $query['statut'];
        }
        unset($query['statut']);
        if(!is_array($statuts)) {
            $statuts = [$statuts];
        }

        $mouvements = [];

        foreach($statuts as $statut) {
            $mouvements = array_merge($mouvements, MouvementLotView::getInstance()->getByIdentifiant($identifiant, $statut)->rows);
        }

        if (isset($query["numero_logement_operateur"])) {
            $query["numero_logement_operateur_slug"] = KeyInflector::slugify(str_replace(" ", "", preg_replace("/[\-,+]*/", "", $query["numero_logement_operateur"])));
            unset($query["numero_logement_operateur"]);
        }

        $res_mouvements = array();
        foreach ($mouvements as $mouvement) {

            $mouvement->value->numero_logement_operateur_slug = KeyInflector::slugify(str_replace(" ", "",preg_replace("/[\-,+]*/", "", $mouvement->value->numero_logement_operateur)));

            $match = true;
            foreach($query as $key => $value) {
                if($mouvement->value->{ $key } != $value) {
                    $match = false;
                    break;
                }
            }

            if(!$match) {
                continue;
            }

            unset($mouvement->value->numero_logement_operateur_slug);

            $res_mouvements[] = $mouvement->value;
        }

        return $res_mouvements;
    }


  public static function getDestinationLibelle($lot) {
    return DRevClient::getLotDestinationsType($lot->destination_type);
  }

}
