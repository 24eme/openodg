<?php
class ControleClient extends acCouchdbClient
{
    const TYPE_MODEL = "Controle";
    const TYPE_COUCHDB = "CONTROLE";

    const CONTROLE_STATUT_A_ORGANISER = "A_ORGANISER";
    const CONTROLE_STATUT_A_PLANIFIER = "A_PLANIFIER";
    const CONTROLE_STATUT_PLANIFIE = "PLANIFIE";
    const CONTROLE_STATUT_EN_MANQUEMENT = "EN_MANQUEMENT";
    const CONTROLE_STATUT_TERMINE = "TERMINE";

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

    public function findAllByStatus($limit = null , $hydrate = acCouchdbClient::HYDRATE_DOCUMENT)
    {
        $controles = [
            self::CONTROLE_STATUT_A_ORGANISER => [],
            self::CONTROLE_STATUT_A_PLANIFIER => [],
            self::CONTROLE_STATUT_PLANIFIE => [],
            self::CONTROLE_STATUT_EN_MANQUEMENT => [],
            self::CONTROLE_STATUT_TERMINE => [],
        ];
        foreach ($this->findAll($limit, $hydrate) as $c) {
            if($c->date_tournee) {
                $controles[self::CONTROLE_STATUT_PLANIFIE][] = $c;
                continue;
            }
            if(count($c->parcelles)) {
                $controles[self::CONTROLE_STATUT_A_PLANIFIER][] = $c;
                continue;
            }

            $controles[self::CONTROLE_STATUT_A_ORGANISER][] = $c;

        }

        return $controles;
    }

}
