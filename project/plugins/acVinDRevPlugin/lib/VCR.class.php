<?php
// Volume Commercialisable de Référence
class VCR
{

    const VCR_COLONNE_MILLESIME = 0;
    const VCR_COLONNE_CVI = 1;
    const VCR_COLONNE_PRODUIT = 2;
    const VCR_COLONNE_VOLUME = 3;

    public static function getFromCSV($millesime, $cvi, $hashFilter = null)
    {
        if (!self::hasCsv()) {
            return null;
        }
        $volumes = [];
        $configFile = fopen(sfConfig::get('sf_root_dir')."/".sfConfig::get('app_vcr_fichier_csv'),"r");
        while (($line = fgetcsv($configFile, null, ';')) !== false) {
            if (($line[self::VCR_COLONNE_MILLESIME] != $millesime)||($line[self::VCR_COLONNE_CVI] != $cvi)) {
                continue;
            }
            $hash = $line[self::VCR_COLONNE_PRODUIT];
            $volume = floatval(str_replace([",", " "],"",$line[self::VCR_COLONNE_VOLUME]));
            $volumes[$hash] = $volume;
        }
        fclose($configFile);
        if ($hashFilter) {
            return isset($volumes[self::cleanHash($hashFilter)])? $volumes[self::cleanHash($hashFilter)] : null;
        }
        return $volumes;
    }

    public static function cleanHash($hash)
    {
        $from = ['/declaration/', 'declaration/'];
        $to = ['', ''];
        return str_replace($from, $to, $hash);
    }

    public static function hasCsv()
    {
        if (!sfConfig::get('app_vcr_fichier_csv')) {
            return false;
        }
        return file_exists(sfConfig::get('sf_root_dir')."/".sfConfig::get('app_vcr_fichier_csv'));
    }
}
