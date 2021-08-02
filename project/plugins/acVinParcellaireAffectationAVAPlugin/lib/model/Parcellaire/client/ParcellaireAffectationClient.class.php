<?php

class ParcellaireAffectationClient extends acCouchdbClient {

    const TYPE_MODEL = "ParcellaireAffectation";
    const TYPE_COUCHDB = "PARCELLAIREAFFECTATION";
    const TYPE_COUCHDB_PARCELLAIRE_CREMANT = "PARCELLAIRECREMANTAFFECTATION";
    const TYPE_COUCHDB_INTENTION_CREMANT = "INTENTIONCREMANT";
    const DESTINATION_SUR_PLACE = "SUR_PLACE";
    const DESTINATION_CAVE_COOPERATIVE = EtablissementClient::FAMILLE_CAVE_COOPERATIVE;
    const DESTINATION_NEGOCIANT = EtablissementClient::FAMILLE_NEGOCIANT;
    const APPELLATION_ALSACEBLANC = 'ALSACEBLANC';
    const APPELLATION_VTSGN = 'VTSGN';
    const APPELLATION_GRDCRU = 'GRDCRU';
    const APPELLATION_COMMUNALE = 'COMMUNALE';
    const APPELLATION_LIEUDIT = 'LIEUDIT';
    const APPELLATION_CREMANT = 'CREMANT';

    public static $appellations_libelles = array(
            self::APPELLATION_ALSACEBLANC => 'Alsace Blanc',
            self::APPELLATION_GRDCRU => 'Grand Cru',
            self::APPELLATION_COMMUNALE => 'Communale',
            self::APPELLATION_LIEUDIT => 'Lieux dits',
            self::APPELLATION_CREMANT => 'Crémant'
                );

    public static $destinations_libelles = array(
        self::DESTINATION_SUR_PLACE => "Viticulteur - Récoltant",
        self::DESTINATION_CAVE_COOPERATIVE => "Adhérent Cave Coopérative",
        self::DESTINATION_NEGOCIANT => "Vendeur de raisin",
    );

    public static $modes_savoirfaire = array();

    public static function getInstance() {
        return acCouchdbManager::getClient("ParcellaireAffectation");
    }

    public static function getAppellationLibelle($appellationKey)
    {
    	return self::$appellations_libelles[str_replace('appellation_', '', $appellationKey)];
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);

        if ($doc && $doc->type != self::TYPE_MODEL) {

            throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
        }

        return $doc;
    }

    public function findOrCreateFromEtablissement($etablissement, $campagne, $type = self::TYPE_COUCHDB) {
        return $this->findOrCreate($etablissement->identifiant, $campagne, $type);
    }

    public function findOrCreate($cvi, $campagne, $type = self::TYPE_COUCHDB) {
        if (strlen($cvi) != 10) {
            throw new sfException("Le CVI doit avoir 10 caractères : $cvi");
        }
        if (strlen($campagne) != 4)
            throw new sfException("La campagne doit être une année et non " . $campagne);
        $parcellaire = $this->find($this->buildId($cvi, $campagne, $type));
        if (is_null($parcellaire)) {
            $parcellaire = $this->createDoc($cvi, $campagne, $type);
        }

        return $parcellaire;
    }

    public function buildId($identifiant, $campagne, $type = self::TYPE_COUCHDB) {
        $id = "$type-%s-%s";
        return sprintf($id, $identifiant, $campagne);
    }

    public function createDoc($identifiant, $campagne, $type = self::TYPE_COUCHDB) {
        $parcellaire = new ParcellaireAffectation();
        $parcellaire->initDoc($identifiant, $campagne, $type);

        return $parcellaire;
    }

    public function getHistory($identifiant, $type = self::TYPE_COUCHDB, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $campagne_from = "0000";
        $campagne_to = ConfigurationClient::getInstance()->getCampagneManager()->getPrevious(ConfigurationClient::getInstance()->getCampagneManager()->getCurrentNext()) . "";

        $id = "$type-%s-%s";
        return $this->startkey(sprintf($id, $identifiant, $campagne_from))
                        ->endkey(sprintf($id, $identifiant, $campagne_to))
                        ->execute($hydrate);
    }

    public function getAppellationsAndVtSgnKeys($type = self::TYPE_COUCHDB) {
        if ($type == self::TYPE_COUCHDB) {
	        return array_merge(array(
	            self::APPELLATION_GRDCRU => 'Grand Cru',
	            self::APPELLATION_COMMUNALE => 'Communale',
	            self::APPELLATION_LIEUDIT => 'Lieux dits'
	                ),
	            array(self::APPELLATION_VTSGN => 'VT/SGN')
	        );
        }
        return array('CREMANT' => 'Crémant');
    }

    public function getAppellationsKeys($type = self::TYPE_COUCHDB) {
        if ($type == self::TYPE_COUCHDB) {
	        return array(
	            self::APPELLATION_ALSACEBLANC => 'Alsace Blanc',
	            self::APPELLATION_GRDCRU => 'Grand Cru',
	            self::APPELLATION_COMMUNALE => 'Communale',
	            self::APPELLATION_LIEUDIT => 'Lieux dits'
	        );
        }
        return array('CREMANT' => 'Crémant');
    }

    public function getFirstAppellation($type = self::TYPE_COUCHDB) {
        if ($type == self::TYPE_COUCHDB) {
        	return 'GRDCRU';
        }
        return 'CREMANT';
    }

    public function getDateOuverture($type = self::TYPE_COUCHDB) {
        if ($type == self::TYPE_COUCHDB) {
            $dates = sfConfig::get('app_dates_ouverture_parcellaire');
        } elseif ($type == self::TYPE_COUCHDB_PARCELLAIRE_CREMANT) {
            $dates = sfConfig::get('app_dates_ouverture_parcellaire_cremant');
        } elseif ($type == self::TYPE_COUCHDB_INTENTION_CREMANT) {
            $dates = sfConfig::get('app_dates_ouverture_intention_cremant');
        } else {
        	throw new sfException("Le type de parcellaire $type n'existe pas");
        }
        return $dates;
    }

    public function getDateOuvertureDebut($type = self::TYPE_COUCHDB) {
        $dates = $this->getDateOuverture($type);
        return $dates['debut'];
    }

    public function getDateOuvertureFin($type = self::TYPE_COUCHDB) {
        $dates = $this->getDateOuverture($type);
        return $dates['fin'];
    }

    public function isOpen($type = self::TYPE_COUCHDB, $date = null) {
        if (is_null($date)) {
            $date = date('Y-m-d');
        }
        return $date >= $this->getDateOuvertureDebut($type) && $date <= $this->getDateOuvertureFin($type);
    }

}
