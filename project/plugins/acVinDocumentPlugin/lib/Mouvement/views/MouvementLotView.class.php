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


    public function getAffecteSourceAvantMoi($lot)
    {
        if((get_class($lot) == 'stdClass' && isset($lot->leurre) && $lot->leurre) || (get_class($lot) != 'stdClass' && $lot->isLeurre())) {
            return array();
        }
        $mouvements = $this->client
                           ->startkey([
                               Lot::STATUT_AFFECTE_SRC,
                               $lot->declarant_identifiant,
                               $lot->campagne,
                               $lot->unique_id,
                           ])
                           ->endkey([
                               Lot::STATUT_AFFECTE_SRC,
                               $lot->declarant_identifiant,
                               $lot->campagne,
                               $lot->unique_id,
                               array()
                           ])
                           ->getView($this->design, $this->view);
         $mvts_rows = array();
         $document_id = null;
         if (get_class($lot) != 'stdClass') {
             $document_id = $lot->getDocument()->_id;
         }elseif (isset($lot->document_id)) {
             $document_id = $lot->document_id;
         }
         foreach($mouvements->rows as $r) {
             if ($document_id && ($r->id == $document_id)) {
                 break;
             }
             $mvts_rows[] = $r;
         }
         return $mvts_rows;
     }

    public function getNombreAffecteSourceAvantMoi($lot)
    {
        return count($this->getAffecteSourceAvantMoi($lot));
    }

    public function find($identifiant, $query) {
        $mvts = $this->getMouvements($identifiant, $query);
        if (!count($mvts)) {
            return null;
        }
        return $mvts[0];
    }

    public function getMouvements($identifiant, $query) {
        $statut = null;
        if(isset($query['statut'])) {
            $statut = $query['statut'];
        }
        unset($query['statut']);
        $mouvements = MouvementLotView::getInstance()->getByIdentifiant($identifiant, $statut);

        if (isset($query["numero_logement_operateur"])) {
            $query["numero_logement_operateur"] = KeyInflector::slugify(str_replace(" ", "", $query["numero_logement_operateur"]));
        }

        $res_mouvements = array();
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

            $res_mouvements[] = $mouvement->value;
        }

        return $res_mouvements;
    }


  public static function getDestinationLibelle($lot) {
    $libelles = DRevClient::$lotDestinationsType;
    return (isset($libelles[$lot->destination_type]))? $libelles[$lot->destination_type] : '';
  }

}
