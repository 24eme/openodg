<?php
class ControleClient extends acCouchdbClient
{
    const TYPE_MODEL = "Controle";
    const TYPE_COUCHDB = "CONTROLE";

    public static function getInstance()
    {
        return acCouchdbManager::getClient("Controle");
    }

    public function findByArgs($identifiant, $date)
    {
        $id = self::TYPE_COUCHDB . '-' . $identifiant . '-' . $date;
        return $this->find($id);
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false)
    {
        $doc = parent::find($id, $hydrate, $force_return_ls);
        if ($doc && $doc->type != self::TYPE_MODEL) {
            sfContext::getInstance()->getLogger()->info("ControleClient::find()".sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
            throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
        }
        return $doc;
    }

    public function findOrCreate($identifiant, $date = null, $type = self::TYPE_COUCHDB)
    {
        if (!$date) {
            $date = date('Ymd');
        }
        $controle = $this->findPreviousByIdentifiantAndDate($identifiant, $date);
        if ($controle && $controle->date == $date) {
            return $controle;
        }
        $controle = new Controle();
        $controle->initDoc($identifiant, $date);
        return $controle;
    }

    public function findPreviousByIdentifiantAndDate($identifiant, $date, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT)
    {
        $h = $this->getHistory($identifiant, $date, $hydrate);
        if (!count($h)) {
            return NULL;
        }
        $h = $h->getDocs();
        end($h);
        $doc = $h[key($h)];
        return $doc;
    }

    public function getLastByCampagne($identifiant, $campagne, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT)
    {
        $date = ConfigurationClient::getInstance()->getCampagneVinicole()->getDateFinByCampagne($campagne);
        return $this->findPreviousByIdentifiantAndDate($identifiant, $date, $hydrate);
    }

    public function getLast($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT)
    {
        return $this->findPreviousByIdentifiantAndDate($identifiant, '9999-99-99');
    }

    public function getHistory($identifiant, $date = '9999-99-99', $hydrate = acCouchdbClient::HYDRATE_DOCUMENT, $dateDebut = "0000-00-00")
    {
        return $this->startkey(sprintf(self::TYPE_COUCHDB."-%s-%s", $identifiant, str_replace('-', '', $dateDebut)))
                    ->endkey(sprintf(self::TYPE_COUCHDB."-%s-%s", $identifiant, str_replace('-', '', $date)))->execute($hydrate);
    }

    public function findAll($limit = null, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT)
    {
    	$view = $this
            ->startkey(sprintf(self::TYPE_COUCHDB."-%s-%s", "AAA0000000", "00000000"))
    	    ->endkey(sprintf(self::TYPE_COUCHDB."-%s-%s", "ZZZ9999999", "99999999"));
    	if ($limit) {
    		$view->limit($limit);
    	}
    	return $view->execute($hydrate)->getDatas();
    }
}
