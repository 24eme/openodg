<?php

class Tournee extends Degustation
{
    public function __construct()
    {
        parent::__construct();
        $this->type = TourneeClient::TYPE_MODEL;
    }

    public function constructId() {
		$date = new DateTime($this->date);

        $this->set('_id', TourneeClient::TYPE_COUCHDB."-".$date->format('YmdHi'));

        $this->campagne = ConfigurationClient::getInstance()->getCampagneVinicole()->getCampagneByDate($date->format('Y-m-d'));
    }
    public function getLotsBySecteur($withAllSecteurs = true) {
        $sans_secteurs = array(DegustationClient::DEGUSTATION_SANS_SECTEUR => array());
        foreach(parent::getLotsBySecteur() as $secteur => $llots) {
            $sans_secteurs[DegustationClient::DEGUSTATION_SANS_SECTEUR] = array_merge($sans_secteurs[DegustationClient::DEGUSTATION_SANS_SECTEUR], $llots);
        }
        return $sans_secteurs;
    }

    public function generateMouvementsLots()
    {
        $this->clearMouvementsLots();

        foreach ($this->lots as $lot) {
            if ($lot->isLeurre()) {
                continue;
            }
            $lot->updateDocumentDependances();
            $lot->updateSpecificiteWithDegustationNumber();
            $statut = $lot->statut;
            switch($statut) {
                case Lot::STATUT_PRELEVE:
                case Lot::STATUT_ATTENTE_PRELEVEMENT:
                        $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_ATTENTE_PRELEVEMENT));
                default:
                    break;
            }
            if($lot->isAnnule()) {
                $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_ANNULE));
            }
            if($lot->isPreleve()) {
                $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_PRELEVE, '', $lot->preleve));
            }
            if ($lot->isChange()) {
                continue;
            }
            if ($lot->isAffecte()) {
                $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_AFFECTE_SRC, Lot::generateTextePassageMouvement($lot->getNombrePassage() + 1)));
            }elseif($lot->isAffectable()) {
                $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_AFFECTABLE_PRELEVE, Lot::generateTextePassageMouvement($lot->getNombrePassage() + 1)));
            }
        }
    }

    public function isTournee() {
        return true;
    }

    public function save($saveDependants = true) {
        foreach($this->lots as $lot) {
            if($lot->isPreleve()) {
                $lot->affectable = true;
            }
        }

        $saved = parent::save($saveDependants);

        return $saved;
    }
}
