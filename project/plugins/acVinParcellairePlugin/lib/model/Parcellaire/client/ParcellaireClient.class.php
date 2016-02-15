<?php

class ParcellaireClient extends acCouchdbClient {

    const TYPE_MODEL = "Parcellaire";
    const TYPE_COUCHDB = "PARCELLAIRE";
    const TYPE_COUCHDB_PARCELLAIRE_CREMANT = "PARCELLAIRECREMANT";
    const DESTINATION_SUR_PLACE = "SUR_PLACE";
    const DESTINATION_CAVE_COOPERATIVE = EtablissementClient::FAMILLE_CAVE_COOPERATIVE;
    const DESTINATION_NEGOCIANT = EtablissementClient::FAMILLE_NEGOCIANT;
    const APPELLATION_ALSACEBLANC = 'ALSACEBLANC';
    const APPELLATION_VTSGN = 'VTSGN';
    const APPELLATION_GRDCRU = 'GRDCRU';
    const APPELLATION_COMMUNALE = 'COMMUNALE';
    const APPELLATION_LIEUDIT = 'LIEUDIT';

    public static $destinations_libelles = array(
        self::DESTINATION_SUR_PLACE => "Viticulteur - Récoltant",
        self::DESTINATION_CAVE_COOPERATIVE => "Adhérent Cave Coopérative",
        self::DESTINATION_NEGOCIANT => "Vendeur de raisin",
    );

    public static function getInstance() {
        return acCouchdbManager::getClient("Parcellaire");
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);

        if ($doc && $doc->type != self::TYPE_MODEL) {

            throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
        }

        return $doc;
    }

    public function findOrCreateFromEtablissement($etablissement, $campagne, $cremant = false) {
        return $this->findOrCreate($etablissement->identifiant, $campagne, $cremant);
    }

    public function findOrCreate($cvi, $campagne, $cremant = false) {
        if (strlen($cvi) != 10) {
            throw new sfException("Le CVI doit avoir 10 caractères : $cvi");
        }
        if (strlen($campagne) != 4)
            throw new sfException("La campagne doit être une année et non " . $campagne);
        $parcellaire = $this->find($this->buildId($cvi, $campagne, $cremant));
        if (is_null($parcellaire)) {
            $parcellaire = $this->createDoc($cvi, $campagne, $cremant);
        }

        return $parcellaire;
    }

    public function buildId($identifiant, $campagne, $cremant = false) {
        $id = (!$cremant) ? "PARCELLAIRE-%s-%s" : "PARCELLAIRECREMANT-%s-%s";
        return sprintf($id, $identifiant, $campagne);
    }

    public function createDoc($identifiant, $campagne, $cremant = false) {
        $parcellaire = new Parcellaire();
        $parcellaire->initDoc($identifiant, $campagne, $cremant);

        return $parcellaire;
    }

    public function getHistory($identifiant, $cremant = false, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $campagne_from = "0000";
        $campagne_to = ConfigurationClient::getInstance()->getCampagneManager()->getPrevious(ConfigurationClient::getInstance()->getCampagneManager()->getCurrentNext()) . "";

        $id = (!$cremant) ? "PARCELLAIRE-%s-%s" : "PARCELLAIRECREMANT-%s-%s";
        return $this->startkey(sprintf($id, $identifiant, $campagne_from))
                        ->endkey(sprintf($id, $identifiant, $campagne_to))
                        ->execute($hydrate);
    }

    public function getAppellationsAndVtSgnKeys($parcellaireCremant = false) {
        if ($parcellaireCremant) {
            return array('CREMANT' => 'Crémant');
        }
        return array_merge(array(
            self::APPELLATION_GRDCRU => 'Grand Cru',
            self::APPELLATION_COMMUNALE => 'Communale',
            self::APPELLATION_LIEUDIT => 'Lieux dits'
                ),
            array(self::APPELLATION_VTSGN => 'VT/SGN')
        );
    }

    public function getAppellationsKeys($parcellaireCremant = false) {
        if ($parcellaireCremant) {
            return array('CREMANT' => 'Crémant');
        }
        return array(
            self::APPELLATION_GRDCRU => 'Grand Cru',
            self::APPELLATION_COMMUNALE => 'Communale',
            self::APPELLATION_LIEUDIT => 'Lieux dits'
        );
    }

    public function getFirstAppellation($parcellaireCremant = false) {
        if ($parcellaireCremant) {
            return 'CREMANT';
        }
        return 'GRDCRU';
    }

    public function getDateOuvertureDebut($parcellaireCremant = false) {
        if ($parcellaireCremant) {
            $dates = sfConfig::get('app_dates_ouverture_parcellaire_cremant');
        } else {
            $dates = sfConfig::get('app_dates_ouverture_parcellaire');
        }

        return $dates['debut'];
    }

    public function getDateOuvertureFin($parcellaireCremant = false) {
        if ($parcellaireCremant) {
            $dates = sfConfig::get('app_dates_ouverture_parcellaire_cremant');
        } else {
            $dates = sfConfig::get('app_dates_ouverture_parcellaire');
        }

        return $dates['fin'];
    }

    public function isOpen($parcellaireCremant = false, $date = null) {
        if (is_null($date)) {

            $date = date('Y-m-d');
        }

        return $date >= $this->getDateOuvertureDebut($parcellaireCremant) && $date <= $this->getDateOuvertureFin($parcellaireCremant);
    }

}
