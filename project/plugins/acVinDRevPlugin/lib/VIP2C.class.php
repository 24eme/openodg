<?php

class VIP2C
{

    const VIP2C_COLONNE_MILLESIME = 0;
    const VIP2C_COLONNE_CVI = 3;
    const VIP2C_COLONNE_PRODUIT = 6;
    const VIP2C_COLONNE_VOLUME = 7;

    static $csv_seuil = [];
    static $infos = [];

    public static function gatherInformations($doc, $millesime)
    {
        if (empty(self::$infos) === false) {
            return self::$infos;
        }

        $infosProduits = self::getInfosFromCSV($doc, $millesime);

        if ($infosProduits === null) {
            self::$infos = ['produits' => []];
            return self::$infos;
        }

        $hashesRegex = array_column($infosProduits, 'hash_regex');

        $volumes = array_fill_keys($hashesRegex, 0);
        $hashProduits = array_fill_keys($hashesRegex, []);
        $contrats = [];
        foreach ($doc->getLots() as $produit) {
            $drevHash = $produit->getConfig()->getHash();
            foreach ($hashesRegex as $hash) {
                if (self::isHashMatch($hash, $drevHash) === true && in_array($drevHash, $hashProduits[$hash]) === false) {
                    $volumes[$hash] += $doc->getVolumeRevendiqueLotsMillesimeCourantByAppellations($drevHash);
                    $hashProduits[$hash][] = $drevHash;
                    $contrats[$hash] = self::getContratsFromAPI($doc->declarant->cvi, $millesime, $drevHash);
                }
            }
        }

        $infosProduits = array_map(function ($value) use ($volumes, $hashProduits, $contrats) {
            $value['volume'] = $volumes[$value['hash_regex']];
            $value['hashes'] = $hashProduits[$value['hash_regex']];
            $value['contrats'] = (isset($contrats[$value['hash_regex']]))? $contrats[$value['hash_regex']] : [];
            return $value;
        }, $infosProduits);

        $infosProduits = array_filter($infosProduits, function ($value) {
            return count($value['hashes']) > 0;
        });

        self::$infos['produits'] = $infosProduits;
        return self::$infos;
    }

    public static function getContratsAPIURL($cvi, $millesime)
    {
        $millesime = explode('-', $millesime)[0];
        $api_link = sfConfig::get('app_api_contrats_link');
        $secret = sfConfig::get('app_api_contrats_secret');
        if (!$api_link || !$secret) {
            return array();
        }

        if ($millesime < self::getConfigMillesimeVolumeSeuil()) {
            return array();
        }

        $epoch = (string) time();

        $md5 = md5($secret."/".$cvi."/".$millesime."/".$epoch);
        return $api_link."/".$cvi."/".$millesime."/".$epoch."/".$md5;
    }

    public static function getContratsFromAPI($cvi, $millesime, $hash_produit = null)
    {
        $millesime = explode('-', $millesime)[0];
        $url = self::getContratsAPIURL($cvi, $millesime);
        if (!$url) {
            return array();
        }
        $content = file_get_contents($url);

        $result = json_decode($content,true);

        if(!$result) {
            return [];
        }

        $todelete = array();
        if ($hash_produit) {
            $confProduit = ConfigurationClient::getInstance()->getConfiguration()->get($hash_produit);
        }
        foreach($result as $contratid => $data) {
            if ($hash_produit && !self::isHashMatch($hash_produit, $data['produit']) && $confProduit->code_douane != $data['code_douane']) {
                $todelete[] = $contratid;
            }
        }
        foreach($todelete as $contratid) {
            unset($result[$contratid]);
        }
        return($result);
    }

    public static function getInfosFromCSV($doc, $millesime){
        if(! self::hasVolumeSeuil() || !$doc->declarant->cvi){
            return null;
        }

        $configFile = fopen(sfConfig::get('sf_root_dir')."/".sfConfig::get('app_api_contrats_fichier_csv'),"r");

        $volumes = array();
        while (($line = fgetcsv($configFile)) !== false) {
            if ($line[self::VIP2C_COLONNE_MILLESIME] != $millesime) {
                continue;
            }

            if ($line[self::VIP2C_COLONNE_CVI] !== $doc->declarant->cvi) {
                continue;
            }

            $defautHash = str_replace(['genres/|','|'], ['genres/TRANQ', 'DEFAUT'], $line[self::VIP2C_COLONNE_PRODUIT]);
            $volumes[] = [
                "hash_regex"  => $line[self::VIP2C_COLONNE_PRODUIT],
                "volume_max"  => str_replace(",","",$line[self::VIP2C_COLONNE_VOLUME]),
                "libelle" => $doc->getConfiguration()->declaration->get($defautHash)->getLibelleComplet()
            ];
        }
        fclose($configFile);

        return $volumes;
    }

    public static function getConfigCampagneVolumeSeuil() {
        return DRevConfiguration::getInstance()->getCampagneVolumeSeuil();
    }

    public static function getConfigMillesimeVolumeSeuil() {
        return substr(self::getConfigCampagneVolumeSeuil(), 0, 4);
    }

    public static function hasVolumeSeuil() {
        return DRevConfiguration::getInstance()->hasVolumeSeuil();
    }

    public static function cleanHash($hash) {
        $from = ['/declaration/', 'declaration/'];
        $to = ['', ''];

        return str_replace($from, $to, $hash);
    }

    public static function isHashMatch($regexp, $hash) {
        $hashes = explode('|',self::cleanHash($regexp));
        $hashCleaned = self::cleanHash($hash);

        foreach ($hashes as $h) {
            if (strpos($hashCleaned, $h) === false) {
                return false;
            }
        }
        return true;
    }

}
