<?php

class ParcellaireClient extends acCouchdbClient {

    const TYPE_MODEL = "Parcellaire";
    const TYPE_COUCHDB = "PARCELLAIRE";
    
    const DESTINATION_SUR_PLACE = "SUR_PLACE";
    const DESTINATION_CAVE_COOPERATIVE = EtablissementClient::FAMILLE_CAVE_COOPERATIVE;
    const DESTINATION_NEGOCIANT = EtablissementClient::FAMILLE_NEGOCIANT;

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

    public function findOrCreateFromEtablissement($etablissement, $campagne) {
        return $this->findOrCreate($etablissement->identifiant, $campagne);
    }

    public function findOrCreate($cvi, $campagne) {
        if (strlen($cvi) != 10) {
            throw new sfException("Le CVI doit avoir 10 caractères : $cvi");
        }
        if (strlen($campagne) != 4)
            throw new sfException("La campagne doit être une année et non " . $campagne);
        $parcellaire = $this->find($this->buildId($cvi, $campagne));
        if (is_null($parcellaire)) {
            $parcellaire = $this->createDoc($cvi, $campagne);
        }

        return $parcellaire;
    }

    public function buildId($identifiant, $campagne) {
        return sprintf("PARCELLAIRE-%s-%s", $identifiant, $campagne);
    }

    public function createDoc($identifiant, $campagne) {
        $parcellaire = new Parcellaire();
        $parcellaire->initDoc($identifiant, $campagne);

        return $parcellaire;
    }

    public function getHistory($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $campagne_from = "0000";
        $campagne_to = ConfigurationClient::getInstance()->getCampagneManager()->getPrevious(ConfigurationClient::getInstance()->getCampagneManager()->getCurrent()) . "";

        return $this->startkey(sprintf("PARCELLAIRE-%s-%s", $identifiant, $campagne_from))
                        ->endkey(sprintf("PARCELLAIRE-%s-%s", $identifiant, $campagne_to))
                        ->execute($hydrate);
    }

    public function getAppellationsKeys() {
        return array(
            'GRDCRU' => 'Grand Crus',
            'COMMUNALE' => 'Communale',
            'LIEUDIT' => 'Lieux dits',
            'CREMANT' => 'Crémant');
    }

    public function getFirstAppellation() {
        return 'GRDCRU';
    }

}
