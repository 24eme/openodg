<?php

class AireClient extends acCouchdbClient {

    const PARCELLAIRE_AIRE_TOTALEMENT = 'OUI';
    const PARCELLAIRE_AIRE_PARTIELLEMENT = 'PARTIEL';
    const PARCELLAIRE_AIRE_EN_ERREUR = 'ERREUR';

    const PARCELLAIRE_AIRE_GENERIC_AIRE = 'aire';

    const PARCELLAIRE_AIRE_HORSDELAIRE = false;

    private $cache_find = array();
    private $cache_all_aires = array();
    private $cache_aires_communes = array();

    public static function getInstance()
    {
      return acCouchdbManager::getClient("Aire");
    }

    const TYPE_MODEL = "Aire";
    const TYPE_COUCHDB = "AIRE";

    public function getDelimitationCommuneDelimitationCache($commune_insee, $denom_id) {
        return CacheFunction::cache('model', "AireClient::getDelimitationCommuneDelimitation", array($commune_insee, $denom_id));
    }

    public static function getHttpGeojsonFromCommuneDelimitation($commune_insee, $inao_denomination_id) {
        $dep = substr($commune_insee,0,2);
        $url_aire = "https://raw.githubusercontent.com/24eme/opendatawine/master/delimitation_aoc/".$dep."/".$commune_insee."/".$inao_denomination_id.".geojson";
        $contents = @file_get_contents($url_aire);
        return $contents;
    }

    public static function getHttpDelimitationsFromCommune($commune_insee) {
        $dep = substr($commune_insee,0,2);
        $url_aire = "https://raw.githubusercontent.com/24eme/opendatawine/master/delimitation_aoc/".$dep."/".$commune_insee."/denominations.json";
        $contents = @file_get_contents($url_aire);
        return $contents;
    }

    public static function getDelimitationsArrayFromCommune($commune_insee) {
        $json = json_decode(self::getHttpDelimitationsFromCommune($commune_insee));
        $delimitations = array();
        foreach ($json as $k => $d) {
            $delimitations[] = str_replace('.geojson', '', $d);
        }
        return $delimitations;
    }

    public static function getCommunesArrayFromDenominationId($denom_id) {
        $url = "https://raw.githubusercontent.com/24eme/opendatawine/master/denominations/".sprintf('%05d', $denom_id).".json";
        $contents = @file_get_contents($url);
        return json_decode($contents, JSON_OBJECT_AS_ARRAY);

    }

    public function createOrUpdateAireFromHttp($commune_insee, $inao_denomination_id) {
        $geojson = self::getHttpGeojsonFromCommuneDelimitation($commune_insee, $inao_denomination_id);
        if (!$geojson) {
            throw new sfException("no denomination $inao_denomination_id for commune $commune_insee");
        }
        $aire = $this->findOrCreate($commune_insee, $inao_denomination_id);
        $aire->geojson = $geojson;
        return $aire;
    }

    public function findOrCreate($commune_insee, $inao_denomination_id) {
        $aire = $this->findByCommuneAndINAO($commune_insee, $inao_denomination_id);
        if (!$aire) {
            $aire = new Aire();
            $aire->initDoc($commune_insee, $inao_denomination_id);
        }
        return $aire;
    }

    protected function findByCommuneAndINAO($commune_insee, $inao_denomination_id) {
        if (!isset($this->cache_find[$commune_insee.$inao_denomination_id])) {
            $this->cache_find[$commune_insee.$inao_denomination_id] = $this->find($this->constructionId($commune_insee, $inao_denomination_id));
        }
        return $this->cache_find[$commune_insee.$inao_denomination_id];
    }

    public function constructionId($commune_insee, $inao_denomination_id) {
        return sprintf('%s-%05d-%05d', self::TYPE_COUCHDB, $commune_insee, $inao_denomination_id);
    }

    public function getAireIdsFromCommune($commune_insee) {
        if (!isset($this->cache_all_aires[$commune_insee])) {
            $res = $this->getAllDocsByType(self::TYPE_COUCHDB.'-'.$commune_insee, 10000);
            $this->cache_all_aires[$commune_insee] = array();
            foreach($res->rows as $r) {
                $this->cache_all_aires[$commune_insee][] = $r->id;
            }
        }
        return $this->cache_all_aires[$commune_insee];
    }

    public function getAiresForInseeCommunes($communes) {
        $communes_hash = implode('-', $communes);
        if (!isset($this->cache_aires_communes[$communes_hash])) {
            $this->cache_aires_communes[$communes_hash] = $this->getAiresForInseeCommunesDirect($communes);
        }
        return $this->cache_aires_communes[$communes_hash];

    }

    public function getAiresForInseeCommunesDirect($communes) {
        $aires = array();
        foreach($communes as $c) {
            foreach($this->getAireIdsFromCommune($c) as $id) {
                $a = $this->find($id);
                if ($a) {
                    $aires[] = $a;
                }
            }
        }
        return $aires;
    }

    public function getMergedAiresForInseeCommunes($communes) {
        $aires = array();
        foreach($this->getAiresForInseeCommunes($communes) as $a) {
            if (!isset($aires[$a->denomination_identifiant])) {
                $aires[$a->denomination_identifiant] = array('json' => array(), 'pseudo_aire' => $a);
            }
            $aires[$a->denomination_identifiant]['json'][] = $a->geojson;
        }
        $pseudo_aires = array();
        foreach($aires as $id => $o) {
            $pseudo_aires[$id] = $o['pseudo_aire'];
            $pseudo_aires[$id]->commune_identifiant = null;
            $pseudo_aires[$id]->commune_libelle = null;
            $pseudo_aires[$id]->_id = null;
            $pseudo_aires[$id]->_rev = 'yyyy';
            $pseudo_aires[$id]->denomination_identifiant = $id;
            $pseudo_aires[$id]->setPseudoGeojsons($o['json']);
        }
        return $pseudo_aires;
    }

    public function getPcFromCommuneGeoParcelleAndAire($commune_insee, ParcellaireParcelle $parcelle, $aire_nom) {
        foreach($this->getAiresForInseeCommunes(array($commune_insee)) as $a) {
            if ($a->denomination_libelle != $aire_nom) {
                continue;
            }
            $geoparcelle = $parcelle->getGeoParcelle();
            return $a->getPcInAire($geoparcelle);
        }
        return 1;
    }

    public function getIsInAiresFromCommuneAndGeoParcelle($commune_insee, ParcellaireParcelle $parcelle) {
        $is_in_aires = array();
        try {
          $geoparcelle = $parcelle->getGeoParcelle();
          foreach($this->getAiresForInseeCommunes(array($commune_insee)) as $a) {
            try {
                if (!count($geoparcelle->getComponents())) {
                    $is_in_aires[AireClient::PARCELLAIRE_AIRE_GENERIC_AIRE] = AireClient::PARCELLAIRE_AIRE_EN_ERREUR;
                }
                $iia = $a->isInAire($geoparcelle);
                if ($iia == AireClient::PARCELLAIRE_AIRE_HORSDELAIRE) {
                    continue;
                }
                $is_in_aires[$a->denomination_libelle] = $iia;
            } catch(Exception $e) {
                if (sfConfig::get('sf_environment') == 'dev') {
                    throw new sfException('error on '.$a->denomination_libelle.' (AIRE-'.$commune_insee.'-'.$a->denomination_identifiant.') : '.$e->getMessage());
                }
                if ($a->exist('denomination_libelle')) {
                    $is_in_aires[$a->denomination_libelle] = AireClient::PARCELLAIRE_AIRE_EN_ERREUR;
                }else{
                    $is_in_aires[AireClient::PARCELLAIRE_AIRE_GENERIC_AIRE] = AireClient::PARCELLAIRE_AIRE_EN_ERREUR;
                }
            }
          }
        } catch(Exception $e) {
            if (sfConfig::get('sf_environment') == 'dev') {
                throw new sfException('Erreur avec la parcelle ('.$parcelle->getHash().') : '.$e->getMessage());
            }
            $is_in_aires['parcelle'] = AireClient::PARCELLAIRE_AIRE_EN_ERREUR;
        }
        return $is_in_aires;
    }

}
