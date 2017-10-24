<?php

class TravauxMarcClient extends acCouchdbClient {
    const TYPE_MODEL = "TravauxMarc";
    const TYPE_COUCHDB = "TRAVAUXMARC";

    public static function getInstance()
    {
        return acCouchdbManager::getClient("TravauxMarc");
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);

        if($doc && $doc->type != self::TYPE_MODEL) {

            throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
        }

        return $doc;
    }

    public function createDoc($identifiant, $campagne, $papier = false)
    {
        $travauxmarc = new TravauxMarc();
        $travauxmarc->initDoc($identifiant, $campagne);

        if($papier) {
            $travauxmarc->add('papier', 1);
        }

        $travauxmarcPrecedente = $this->find(self::TYPE_COUCHDB."-".$identifiant."-".($campagne-1));
        if($travauxmarcPrecedente) {
            $travauxmarc->adresse_distillation = $travauxmarcPrecedente->adresse_distillation;
        }

        return $travauxmarc;
    }

    public function getHistory($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $campagne_from = "0000";
        $campagne_to = ConfigurationClient::getInstance()->getCampagneManager()->getCurrent();

        return $this->startkey(sprintf(self::TYPE_COUCHDB."-%s-%s", $identifiant, $campagne_from))
                    ->endkey(sprintf(self::TYPE_COUCHDB."-%s-%s", $identifiant, $campagne_to))
                    ->execute($hydrate);
    }

    public function getDateOuvertureDebut() {
        $dates = sfConfig::get('app_dates_ouverture_travauxmarc');

        return $dates['debut'];
    }

    public function getDateOuvertureFin() {
        $dates = sfConfig::get('app_dates_ouverture_travauxmarc');

        return $dates['fin'];
    }

    public function isOpen($date = null) {
        if(is_null($date)) {

            $date = date('Y-m-d');
        }

        return $date >= $this->getDateOuvertureDebut() && $date <= $this->getDateOuvertureFin();
    }
}
