<?php

class DeclarationTousView extends acCouchdbView
{
    const KEY_REGION = 0;
    const KEY_TYPE = 1;
    const KEY_CAMPAGNE = 2;
    const KEY_IDENTIFIANT = 3;
    const KEY_MODE = 4;
    const KEY_STATUT = 5;
    const KEY_PRODUIT = 6;
    const KEY_DATE = 7;
    const KEY_INFOS = 8;
    const KEY_RAISON_SOCIALE = 9;
    const KEY_COMMUNE = 10;
    const KEY_EMAIL = 11;
    const KEY_CVI = 12;

    const GROUP_LEVEL_CAMPAGNE = 3;
    const FILTER_KEY_DEFAULT_REGION = '';

    const MODE_TELDECLARATION = "Télédeclaration";
    const MODE_SAISIE_INTERNE = "Saisie interne";
    const MODE_IMPORTE = "Importé";

    const STATUT_BROUILLON = "Brouillon";
    const STATUT_A_VALIDER = "À valider";
    const STATUT_VALIDE = "Validé";
    const STATUT_EN_ATTENTE = "En attente";
    const STATUT_A_APPROUVER = "À approuver";

    public static function getInstance() {
        return acCouchdbManager::getView('declaration', 'tous');
    }

    public static function constructIdentifiantDocument($result,$facetName =""){
      return $result->id.$facetName;
    }

    public function getByTypeCampagneIdentifiant($typeDoc, $campagne, $identifiant, $region = "") {
        if (!$region) {
            $region = self::FILTER_KEY_DEFAULT_REGION;
        }
        return $this->client->startkey([$region, $typeDoc, "".$campagne, $identifiant])
                            ->endkey(array($region, $typeDoc, "".$campagne, $identifiant, array()))
                            ->reduce(false)
                            ->getView($this->design, $this->view);
    }

    public function getByTypeCampagne($typeDoc, $campagne, $region = "") {
        if (!$region) {
            $region = self::FILTER_KEY_DEFAULT_REGION;
        }
        return $this->client->startkey([$region, $typeDoc, "".$campagne])
                            ->endkey(array($region, $typeDoc, "".$campagne, array()))
                            ->reduce(false)
                            ->getView($this->design, $this->view);
    }
}
