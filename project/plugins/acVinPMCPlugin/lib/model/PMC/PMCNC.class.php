<?php

class PMCNC extends PMC
{
    public function __construct()
    {
        parent::__construct();
        $this->type = PMCNCClient::TYPE_MODEL;
    }

    public function constructId() {
        if (! $this->date) {
            $this->date = date("Y-m-d H:i:s");
        }

        $idDate = preg_replace('/[^0-9]/', '', $this->date);

        if (strlen($idDate) < 8) {
            throw new sfException("Mauvais format de date pour la construction de l'id");
        }

        $id = 'PMCNC-' . $this->identifiant . '-' . $idDate;
        $this->set('_id', $id);
    }

    /** Facturation **/
    public function aFacturer()
    {
        $pmcs = PMCNCClient::getInstance()->findPMCNCsByCampagne($this->identifiant, $this->campagne);

        uasort($pmcs, function ($a, $b) {
            return $a->_id > $b->_id;
        });

        if (current($pmcs)->_id === $this->_id) {
            return true;
        }

        return false;
    }

    public function getRegions() {
        return array(Organisme::getOIRegion());
    }

    public function getLotOrigine() {
        return $this->lots[0]->getLotOrigine();
    }

    public function getMother() {
        if (!isset($this->lots[0])) {
            return null;
        }
        if (!$this->lots[0]->getLotProvenance()) {
            return null;
        }
        return $this->lots[0]->getLotProvenance()->getDocument();
    }
}
