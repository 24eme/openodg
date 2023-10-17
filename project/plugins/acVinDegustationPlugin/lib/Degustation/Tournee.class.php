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
    public function getLotsBySecteur() {
        $sans_secteurs = array(DegustationClient::DEGUSTATION_SANS_SECTEUR => array());
        foreach(parent::getLotsBySecteur() as $secteur => $llots) {
            $sans_secteurs[DegustationClient::DEGUSTATION_SANS_SECTEUR] = array_merge($sans_secteurs[DegustationClient::DEGUSTATION_SANS_SECTEUR], $llots);
        }
        return $sans_secteurs;
    }
}
