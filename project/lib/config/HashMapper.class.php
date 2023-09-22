<?php

class HashMapper {

    public static $hashMapper = null;

    public static function convert($hash) {

        return self::getHashMapper()->convert($hash);
    }

    public static function inverse($hash, $type = 'DR') {

        return self::getHashMapper($type)->inverse($hash, $type);
    }

    public static function getHashMapperCached($type) {

        return new HashMapperCached($type);
    }

    public static function getHashMapper($type = 'DR') {
        if(is_null(self::$hashMapper)) {
            self::$hashMapper = array();
        }
        if (!isset(self::$hashMapper[$type])) {
            self::$hashMapper[$type] = CacheFunction::cache('model', "HashMapper::getHashMapperCached", array($type));
        }

        return self::$hashMapper[$type];
    }

    public static function hashDR2hashDS($hash) {
        return str_replace('/recolte/', '/declaration/', $hash);
    }

}

class HashMapperCached {

    public $convert_hash = array();
    public $inverse_hash = array();

    public function __construct($type = 'DR') {
        $configuration = ConfigurationClient::getCurrent();
        $this->callAll($configuration->declaration, $type);
    }

    public function callAll($child, $type) {
        if(!method_exists($child, "getChildrenNode")) {

            return;
        }

        if(!$child->getChildrenNode()) {

            return;
        }
        foreach($child->getChildrenNode() as $child) {
            $hash = $this->convert($child->getHash());
            $this->inverse($hash, $type);
            $hash = $this->convert($child->getHash()."/");
            $this->inverse($hash, $type);
            $this->callAll($child, $type);
        }
    }

    public function convert($hash) {
        $hashOrigine = $hash;

        if(array_key_exists($hash, $this->convert_hash)) {

            return $this->convert_hash[$hash];
        }

        $hash = preg_replace("|^/recolte|", "/declaration", $hash);
        $hash = preg_replace("|/certification|", "/certifications/AOC_ALSACE", $hash);
        $hash = preg_replace("|/genre/|", "/genres/TRANQ/", $hash);
        $hash = preg_replace("|/genre$|", "/genres/TRANQ", $hash);
        $hash = preg_replace("|/genreVCI|", "/genres/VCI", $hash);
        $hash = preg_replace("|/appellation_([a-zA-Z0-9_-]+)|", "/appellations/$1", $hash);
        $hash = preg_replace("|/mention/|", "/mentions/DEFAUT/", $hash);
        $hash = preg_replace("|/mention$|", "/mentions/DEFAUT", $hash);
        $hash = preg_replace("|/mention([A-Z0-9]+)/|", "/mentions/$1/", $hash);
        $hash = preg_replace("|/mention([A-Z0-9]+)$|", "/mentions/$1", $hash);
        $hash = preg_replace("|/lieu/|", "/lieux/DEFAUT/", $hash);
        $hash = preg_replace("|/lieu$|", "/lieux/DEFAUT", $hash);
        $hash = preg_replace("|/lieu([A-Z0-9]+)/|", "/lieux/$1/", $hash);
        $hash = preg_replace("|/lieu([A-Z0-9]+)$|", "/lieux/$1", $hash);
        $hash = preg_replace("|/couleur/|", "/couleurs/DEFAUT/", $hash);
        $hash = preg_replace("|/couleur$|", "/couleurs/DEFAUT", $hash);
        $hash = preg_replace("|/couleur([a-zA-Z0-9_-]{2,30})/|", "/couleurs/$1/", $hash);
        $hash = preg_replace("|/couleur([a-zA-Z0-9_-]{2,30})$|", "/couleurs/$1", $hash);
        $hash = preg_replace("|/cepage_([a-zA-Z0-9_-]+)|", "/cepages/$1", $hash);
        $hash = str_replace("couleurs/Rouge", "couleurs/rouge", $hash);
        $hash = str_replace("couleurs/Blanc", "couleurs/blanc", $hash);
        $hash = preg_replace("|/genres/TRANQ/appellations/CREMANT|", "/genres/EFF/appellations/CREMANT", $hash);
        $hash = preg_replace("|/certifications/AOC_ALSACE/genres/TRANQ/appellations/VINTABLE|", "/certifications/VINSSIG/genres/TRANQ/appellations/VINTABLE", $hash);

        $this->convert_hash[$hashOrigine] = $hash;

        return $hash;
    }

    public function inverse($hash, $type) {
        $hashOrigine = $hash;

        if(array_key_exists($type.$hash, $this->inverse_hash)) {

            return $this->inverse_hash[$type.$hash];
        }

        $hash = preg_replace("|^/declaration|", "/recolte", $hash);
        $hash = str_replace("/certifications/AOC_ALSACE", "/certification", $hash);
        $hash = str_replace("/certifications/VINSSIG", "/certification", $hash);
        $hash = str_replace("/genres/TRANQ", "/genre", $hash);
        $hash = str_replace("/genres/VCI", "/genreVCI", $hash);
        $hash = preg_replace("|/appellations/([a-zA-Z0-9_-]+)|", "/appellation_$1" , $hash);
        $hash = str_replace("/mentions/DEFAUT/", "/mention/", $hash);
        $hash = str_replace("/mentions/DEFAUT", "/mention", $hash);
        $hash = preg_replace("|/mentions/([A-Z0-9]+)/|", "/mention$1/", $hash);
        $hash = preg_replace("|/mentions/([A-Z0-9]+)$|", "/mention$1", $hash);
        $hash = str_replace("/lieux/DEFAUT/", "/lieu/", $hash);
        $hash = str_replace("/lieux/DEFAUT", "/lieu", $hash);
        $hash = preg_replace("|/lieux/([A-Z0-9]+)/|", "/lieu$1/", $hash);
        $hash = preg_replace("|/lieux/([A-Z0-9]+)$|", "/lieu$1", $hash);
        $hash = str_replace("/couleurs/DEFAUT/", "/couleur/", $hash);
        $hash = preg_replace("|/couleurs/DEFAUT$|", "/couleur",$hash);
        $hash = preg_replace("|/couleurs/([a-zA-Z0-9_-]{2,30})/|", "/couleur$1/", $hash);
        $hash = preg_replace("|/couleurs/([a-zA-Z0-9_-]{2,30})$|", "/couleur$1", $hash);
        $hash = str_replace("couleurblanc", "couleurBlanc", $hash);
        $hash = str_replace("couleurrouge", "couleurRouge", $hash);
        $hash = preg_replace("|/cepages/([a-zA-Z0-9_-]+)|", "/cepage_$1", $hash);
        $hash = str_replace("/genres/EFF", "/genre", $hash);

        if ($type == 'DS') {
            $hash = str_replace('/recolte/', '/declaration/', $hash);
        }

        $this->inverse_hash[$type.$hashOrigine] = $hash;

        return $hash;
    }

}
