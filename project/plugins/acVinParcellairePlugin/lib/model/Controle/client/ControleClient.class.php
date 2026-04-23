<?php
class ControleClient extends acCouchdbClient
{
    const TYPE_MODEL = "Controle";
    const TYPE_COUCHDB = "CONTROLE";

    const CONTROLE_STATUT_A_PLANIFIER = "A_PLANIFIER";
    const CONTROLE_STATUT_A_ORGANISER = "A_ORGANISER";
    const CONTROLE_STATUT_ORGANISE = "ORGANISE";
    const CONTROLE_STATUT_A_NOTIFIER = 'A_NOTIFIER';
    const CONTROLE_STATUT_TOURNEE_TERMINEE_AVEC_MANQUEMENTS_A_TRAITER = "EN_MANQUEMENT";
    const CONTROLE_STATUT_CONTROLE_CLOTURE = "CLOTURE";

    const CONTROLE_TYPE_HABILITATION = "Habilitation";
    const CONTROLE_TYPE_SUIVI = "Suivi de manquements";
    const CONTROLE_TYPE_DOCUMENTAIRE = "Documentaire";
    const CONTROLE_TYPE_CONDITIONS = "Conditions de production";

    const CONTROLE_CLOTURE_LEVER = 'LEVER';
    const CONTROLE_CLOTURE_OC = 'OC';

    public static function getInstance()
    {
        return acCouchdbManager::getClient("Controle");
    }

    public function getTypes() {
        return [ControleClient::CONTROLE_TYPE_CONDITIONS, ControleClient::CONTROLE_TYPE_SUIVI, ControleClient::CONTROLE_TYPE_DOCUMENTAIRE, ControleClient::CONTROLE_TYPE_HABILITATION];
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
            $date = date('Y-m-d');
        }
        $controle = new Controle();
        $controle->initDoc($identifiant, $date);
        return $controle;
    }

    public function findAllByIdentifiant($identifiant, $limit = null, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT)
    {
        if (!$identifiant) {
            return $this->findAll($limit, $hydrate);
        }
        $view = $this
            ->startkey(sprintf(self::TYPE_COUCHDB."-%s-%s", $identifiant, "00000000"))
            ->endkey(sprintf(self::TYPE_COUCHDB."-%s-%s", $identifiant, "99999999"));
    	if ($limit) {
    		$view->limit($limit);
    	}
    	return $view->execute($hydrate)->getDatas();
    }

    public function findAll($limit = null, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $view = $this
            ->startkey(sprintf(self::TYPE_COUCHDB."-%s-%s", "AAA0000000", "00000000"))
            ->endkey(sprintf(self::TYPE_COUCHDB."-%s-%s", "ZZZ9999999", "99999999"));
        if ($limit) {
            $view->limit($limit);
        }
        return $view->execute($hydrate)->getDatas();
    }

    public static function getOrdreStatut($statut)
    {
        $ordre = array(
            self::CONTROLE_STATUT_A_PLANIFIER => 0,
            self::CONTROLE_STATUT_A_ORGANISER => 1,
            self::CONTROLE_STATUT_ORGANISE => 2,
            self::CONTROLE_STATUT_A_NOTIFIER => 3,
            self::CONTROLE_STATUT_TOURNEE_TERMINEE_AVEC_MANQUEMENTS_A_TRAITER => 4,
            self::CONTROLE_STATUT_CONTROLE_CLOTURE => 5
        );
        return $ordre[$statut];
    }

    public function findAllByStatus($identifiant = null, $limit = null , $hydrate = acCouchdbClient::HYDRATE_DOCUMENT)
    {
        $controles = [
            self::CONTROLE_STATUT_A_PLANIFIER => [],
            self::CONTROLE_STATUT_A_ORGANISER => [],
            self::CONTROLE_STATUT_ORGANISE => [],
            self::CONTROLE_STATUT_A_NOTIFIER => [],
            self::CONTROLE_STATUT_TOURNEE_TERMINEE_AVEC_MANQUEMENTS_A_TRAITER => [],
            self::CONTROLE_STATUT_CONTROLE_CLOTURE => [],
        ];
        foreach ($this->findAllByIdentifiant($identifiant, $limit, $hydrate) as $c) {
            $controles[$c->mouvements_statuts[0][2]][] = $c;
        }
        return $controles;
    }

    public function findByManquements($identifiant = null, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT)
    {
        $controles = ControleClient::getInstance()->findAllByStatus($identifiant, null, $hydrate);
        $sorted_controles = $controles[ControleClient::CONTROLE_STATUT_TOURNEE_TERMINEE_AVEC_MANQUEMENTS_A_TRAITER];
        usort($sorted_controles, "ControleClient::sortControlesByDateNotification");
        return $sorted_controles;
    }

    public function findAllByDateTourneeAndAgent($date_tournee, $agent_identifiant)
    {
        $ret = array();
        foreach ($this->findAll() as $c) {
            if ($c->date_tournee === $date_tournee && $c->agent_identifiant === $agent_identifiant) {
                $ret[] = $c;
            }
        }
        return $ret;
    }

    public static function getAllAgents()
    {
        $result = [];
        foreach (CompteTagsView::getInstance()->listByTags('manuel', 'agent_controle') as $k => $v) {
          $result[] = CompteClient::getInstance()->find($v->id);
        }
        return $result;
    }

    public static function sortControlesByDateNotification($controle_a, $controle_b)
    {
        return strtotime($controle_a->notification_date) > strtotime($controle_b->notification_date);
    }

}
