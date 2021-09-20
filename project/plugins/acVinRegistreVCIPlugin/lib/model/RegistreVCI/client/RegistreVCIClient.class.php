<?php

class RegistreVCIClient extends acCouchdbClient implements FacturableClient {
    const TYPE_MODEL = "RegistreVCI";
    const TYPE_COUCHDB = "REGISTREVCI";

    const MOUVEMENT_CONSTITUE = 'constitue';
    const MOUVEMENT_RAFRAICHI = 'rafraichi';
    const MOUVEMENT_COMPLEMENT = 'complement';
    const MOUVEMENT_SUBSTITUTION = 'substitution';
    const MOUVEMENT_DESTRUCTION = 'destruction';
    const MOUVEMENT_STOCKFIN = 'stock_final';

    const LIEU_CAVEPARTICULIERE = 'CAVEPARTICULIERE';

    public static function MOUVEMENT_SENS($m) {
      $sens = array(self::MOUVEMENT_CONSTITUE => 1, self::MOUVEMENT_RAFRAICHI => -1, self::MOUVEMENT_COMPLEMENT => -1, self::MOUVEMENT_DESTRUCTION => -1, self::MOUVEMENT_SUBSTITUTION => -1);
      return $sens[$m];
    }

    public static function MOUVEMENT_LIBELLE($m) {
      $sens = array(self::MOUVEMENT_CONSTITUE => 'Constitué', self::MOUVEMENT_RAFRAICHI => 'Rafraîchi', self::MOUVEMENT_COMPLEMENT => 'Complément', self::MOUVEMENT_DESTRUCTION => 'Destruction', self::MOUVEMENT_SUBSTITUTION => 'Substitution');
      return $sens[$m];
    }


    public static function getInstance()
    {
      return acCouchdbManager::getClient("RegistreVCI");
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);

        if($doc && $doc->type != self::TYPE_MODEL) {

            throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
        }

        return $doc;
    }

    public function findMasterByIdentifiantAndCampagne($identifiant, $campagne, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $registres = DeclarationClient::getInstance()->viewByIdentifiantCampagneAndType($identifiant, $campagne, self::TYPE_MODEL);
        foreach ($registres as $id => $registre) {

            return $this->find($id, $hydrate);
        }

        return null;
    }

    public function findMasterByIdentifiantAndCampagneOrCreate($identifiant, $campagne, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
      $r = $this->findMasterByIdentifiantAndCampagne($identifiant, $campagne, $hydrate);
      if (!$r) {
        $r = $this->createDoc($identifiant, $campagne);
      }
      return $r;
    }

    public function createDoc($identifiant, $campagne)
    {
        if($registrePrecedent = $this->findMasterByIdentifiantAndCampagne($identifiant, ($campagne - 1))) {

            return $registrePrecedent->generateSuivante();
        }

        $registre = new RegistreVCI();
        $registre->initDoc($identifiant, $campagne);
        $etablissement = $registre->getEtablissementObject();

        return $registre;
    }

    public function getHistory($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $campagne_from = "0000";
        $campagne_to = ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_PREMIERE_ANNEE)->getCurrent()."";

        return $this->startkey(sprintf(self::TYPE_COUCHDB."-%s-%s", $identifiant, $campagne_from))
        ->endkey(sprintf(self::TYPE_COUCHDB."-%s-%s_ZZZZZZZZZZZZZZ", $identifiant, $campagne_to))
                    ->execute($hydrate);
    }

    public function findFacturable($identifiant, $campagne) {
        $registre = $this->find('REGISTREVCI-'.str_replace("E", "", $identifiant).'-'.$campagne);

        if(!$registre) {

            return array();
        }

        return array($registre->_id => $registre);
    }

}
