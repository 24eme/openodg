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


    public function getByIdentifiant($identifiant) {

        return $this->client->startkey(array(null, $identifiant))
                            ->endkey(array(null, $identifiant, array()))
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

    public function getNombreDegustationAvantMoi($lot)
    {
        $mouvements = $this->client
                           ->startkey([
                               Lot::STATUT_AFFECTE_DEST,
                               $lot->declarant_identifiant,
                               $lot->unique_id,
                               ""
                           ])
                           ->endkey([
                               Lot::STATUT_AFFECTE_DEST,
                               $lot->declarant_identifiant,
                               $lot->unique_id,
                               $lot->id_document
                           ])
                           ->getView($this->design, $this->view);

        return count($mouvements->rows);
    }

    public function find($identifiant, $query) {
        $mouvements = MouvementLotView::getInstance()->getByIdentifiant($identifiant);

        $mouvement = null;
        foreach ($mouvements->rows as $mouvement) {
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
