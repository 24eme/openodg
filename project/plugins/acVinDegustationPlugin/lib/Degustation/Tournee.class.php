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
}
