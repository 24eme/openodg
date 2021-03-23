<?php

class MouvementLotView extends acCouchdbView
{
    const KEY_STATUT = 0;
    const IDENTIFIANT = 1;

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

    public function getNombrePassage($lot)
    {
        $mouvements = $this->client
                           ->startkey([Lot::STATUT_AFFECTE_DEST, $lot->declarant_identifiant, $lot->unique_id])
                           ->endkey([Lot::STATUT_AFFECTE_DEST, $lot->declarant_identifiant, $lot->unique_id, []])
                           ->getView($this->design, $this->view);

        return count($mouvements->rows);
    }

    public function getDegustationAvantMoi($lot)
    {
        $mouvements = $this->client
                           ->startkey([
                               Lot::STATUT_AFFECTE_SRC,
                               $lot->declarant_identifiant,
                               $lot->unique_id,
                               ""
                           ])
                           ->endkey([
                               Lot::STATUT_AFFECTE_SRC,
                               $lot->declarant_identifiant,
                               $lot->unique_id,
                               $lot->id_document
                           ])
                           ->getView($this->design, $this->view);
         return $mouvements;
     }

    public function getNombreDegustationAvantMoi($lot)
    {
        return count($this->getDegustationAvantMoi($lot)->rows);
    }

    public function find($identifiant, $query) {
        $statut = null;
        if(isset($query['statut'])) {
            $statut = $query['statut'];
        }
        unset($query['statut']);
        $mouvements = MouvementLotView::getInstance()->getByIdentifiant($identifiant, $statut);

        $query["numero_logement_operateur"] = KeyInflector::slugify(str_replace(" ", "", $query["numero_logement_operateur"]));

        $mouvement = null;
        foreach ($mouvements->rows as $mouvement) {

            $mouvement->value->numero_logement_operateur = KeyInflector::slugify(str_replace(" ", "",$mouvement->value->numero_logement_operateur));

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

            return $mouvement->value;
        }

        return null;
    }


  public static function getDestinationLibelle($lot) {
    $libelles = DRevClient::$lotDestinationsType;
    return (isset($libelles[$lot->destination_type]))? $libelles[$lot->destination_type] : '';
  }

}
