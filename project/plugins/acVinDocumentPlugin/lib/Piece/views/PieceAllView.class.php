<?php

class PieceAllView extends acCouchdbView
{

    const KEYS_IDENTIFIANT = 0;
    const KEYS_DATE_DEPOT = 1;
    const KEYS_LIBELLE = 2;
    const KEYS_MIME = 3;
    const KEYS_VISIBILITE = 4;
    const KEYS_SOURCE = 5;

    const VALUES_KEY = 0;
    const VALUES_FICHIERS = 1;

    public static function getInstance() {
        return acCouchdbManager::getView('piece', 'all');
    }

 	public function getAll() {
        return $this->client->getView($this->design, $this->view)->rows;
 	}

    public function getPiecesByEtablissement($etablissement, $startdate = null, $enddate = null) {
    	$start = array($etablissement);
    	$end = array($etablissement);
    	if ($startdate) {
    		$start[] = $startdate;
    		$end[] = ($enddate)? $enddate : $this->getEndISODateForView();
    	}
    	$end[] = array();
        return array_reverse($this->client
            ->startkey($start)
            ->endkey($end)
            ->reduce(false)
            ->getView($this->design, $this->view)->rows);
    }
    
    public function getEndISODateForView() {
    	return '9999-99-99';
    }
}  
