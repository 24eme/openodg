<?php

class DRClient extends acCouchdbClient implements FacturableClient, DouaneClient {

    const TYPE_COUCHDB = 'DR';
	const TYPE_MODEL = 'DR';
    const STATUT_EN_ATTENTE = 'En attente';

    public static function getInstance()
    {
      return acCouchdbManager::getClient("DR");
    }

    public function findByArgs($identifiant, $annee, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT)
    {
    	$id = 'DR-' . $identifiant . '-' . $annee;
    	return $this->find($id, $hydrate);
    }

    public function createDoc($identifiant, $campagne, $papier = false)
    {
        $fichier = new DR();
        $fichier->campagne = $campagne;
        $fichier->initDoc($identifiant);

        if($papier) {
            $fichier->add('papier', 1);
        }

        return $fichier;
    }

    public function findAll($limit = null, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT)
    {
    	$view = $this->startkey(sprintf(self::TYPE_MODEL."-%s-%s", "00000000", "0000"))
    				 ->endkey(sprintf(self::TYPE_MODEL."-%s-%s", "99999999", "9999"));
    	if ($limit) {
    		$view->limit($limit);
    	}
    	return $view->execute($hydrate)->getDatas();
    }

	public function findFacturable($identifiant, $campagne) {
    	$dr = $this->find('DR-'.$identifiant.'-'.$campagne);
        if($dr && !$dr->exist('donnees')) {
            $dr->generateDonnees();
        }

        return $dr;
    }
}
