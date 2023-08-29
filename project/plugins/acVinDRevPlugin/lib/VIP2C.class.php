<?php

class VIP2C
{

    const VIP2C_COLONNE_MILLESIME = 0;
    const VIP2C_COLONNE_CVI = 3;
    const VIP2C_COLONNE_PRODUIT = 6;
    const VIP2C_COLONNE_VOLUME = 7;

    static $csv_seuil;

    public static function getContratsAPIURL($cvi, $campagne)
    {
        $api_link = sfConfig::get('app_api_contrats_link');
        $secret = sfConfig::get('app_api_contrats_secret');
        if (!$api_link || !$secret) {
            return array();
        }

        if ($campagne < VIP2C::getConfigCampagneVolumeSeuil()) {
            return array();
        }

        $millesime = substr($campagne, 0, 4);

        $epoch = (string) time();

        $md5 = md5($secret."/".$cvi."/".$millesime."/".$epoch);
        return $api_link."/".$cvi."/".$millesime."/".$epoch."/".$md5;
    }

    public static function getContratsFromAPI($cvi, $campagne)
    {
        $url = self::getContratsAPIURL($cvi, $campagne);
        if (!$url) {
            return array();
        }
        $content = file_get_contents($url);

        $result = json_decode($content,true);

        return($result);
    }

    public static function getVolumeSeuilFromCSV($cvi, $campagne){
        if(!VIP2C::hasVolumeSeuil()){
            return null;
        }
        $configFile = fopen(sfConfig::get('sf_root_dir')."/".sfConfig::get('app_api_contrats_fichier_csv'),"r");

        $volumes = array();
        while (($line = fgetcsv($configFile)) !== false) {
            if (intval($line[self::VIP2C_COLONNE_MILLESIME]) * 1 != intval($campagne) *1) {
                continue;
            }
            if (!isset($volumes[$line[self::VIP2C_COLONNE_CVI]])) {
                $volumes[$line[self::VIP2C_COLONNE_CVI]] = array();
            }
            $volumes[$line[self::VIP2C_COLONNE_CVI]][$line[self::VIP2C_COLONNE_PRODUIT]] = str_replace(",","",$line[self::VIP2C_COLONNE_VOLUME]);
        }
        fclose($configFile);

        if (!isset($volumes[$cvi])) {
            return null;
        }
        return $volumes[$cvi];
    }

    public static function getVolumeSeuilProduitFromCSV($cvi, $campagne, $hash_produit) {
        if (!self::$csv_seuil[$campagne]) {
            self::$csv_seuil[$campagne] = self::getVolumeSeuilFromCSV($cvi, $campagne);
        }
        if (!isset(self::$csv_seuil[$campagne][$hash_produit])) {
            return null;
        }
        return self::$csv_seuil[$campagne][$hash_produit];
    }

    public static function getConfigCampagneVolumeSeuil() {
        return DRevConfiguration::getInstance()->getCampagneVolumeSeuil();
    }

    public static function getConfigMillesimeVolumeSeuil() {
        return substr(self::getConfigCampagneVolumeSeuil(), 0, 4);
    }

    public static function getProduitsHashWithVolumeSeuil($cvi, $campagne) {
        $r = self::getVolumeSeuilFromCSV($cvi, $campagne);
        if (!$r) {
            return array();
        }
        return array_keys($r);
    }

    public static function hasVolumeSeuil() {
        return DRevConfiguration::getInstance()->hasVolumeSeuil();
    }

}
