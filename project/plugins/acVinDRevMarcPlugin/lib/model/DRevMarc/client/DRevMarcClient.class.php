<?php

class DRevMarcClient extends acCouchdbClient implements FacturableClient {

    const TYPE_MODEL = "DRevMarc";
    const TYPE_COUCHDB = "DREVMARC";

    public static function getInstance()
    {
      return acCouchdbManager::getClient("DRevMarc");
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);

        if($doc && $doc->type != self::TYPE_MODEL) {

            throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
        }

        return $doc;
    }

    public function findFacturable($identifiant, $campagne) {
        $drev = $this->find('DREVMARC-'.str_replace("E", "", $identifiant).'-'.$campagne);
        
        if(!$drev) {
            
            return array();
        }

        if(!$drev->validation_odg) {

            return array();
        }

        return array($drev->_id => $drev);
    }

    public function createDoc($identifiant, $campagne, $papier = false)
    {
        $drevmarc = new DRevMarc();
        $drevmarc->initDoc($identifiant, $campagne);

        if($papier) {
            $drevmarc->add('papier', 1);
        }

        return $drevmarc;
    }

    public function getHistory($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $campagne_from = "0000";
        $campagne_to = ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_PREMIERE_ANNEE)->getCurrent()."";

        return $this->startkey(sprintf("DREVMARC-%s-%s", $identifiant, $campagne_from))
                    ->endkey(sprintf("DREVMARC-%s-%s", $identifiant, $campagne_to))
                    ->execute($hydrate);
    }

    public function getDateOuvertureDebut() {
        $dates = sfConfig::get('app_dates_ouverture_drevmarc');

        return $dates['debut'];
    }

    public function getDateOuvertureFin() {
        $dates = sfConfig::get('app_dates_ouverture_drevmarc');

        return $dates['fin'];
    }

    public function isOpen($date = null) {
        if(is_null($date)) {

            $date = date('Y-m-d');
        }

        return $date >= $this->getDateOuvertureDebut() && $date <= $this->getDateOuvertureFin();
    }
}
