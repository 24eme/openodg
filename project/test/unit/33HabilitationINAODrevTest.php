<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

sfContext::createInstance($configuration);

$t = new lime_test(4);

$inao = new INAOHabilitationCsvFile(dirname(__FILE__).'/../data/INAO_Habilitation.csv');

$csv = $inao->getLignes();
$id_habilite = 0;
$id_non_habilite = 0;

foreach ($csv as $id => $l) {
    if (preg_match('/[0-9]/', $l[INAOHabilitationCsvFile::CSV_CVI]) && preg_match('/[0-9]/', $l[INAOHabilitationCsvFile::CSV_SIRET])) {
        if ($l[INAOHabilitationCsvFile::CSV_VINIFICATEUR]) {
            $id_habilite = $id;
        }else{
            $id_non_habilite = $id;
        }
    }
    if ($id_habilite && $id_non_habilite) {
        break;
    }
}

$t->is($inao->isHabilite($csv[$id_habilite][INAOHabilitationCsvFile::CSV_CVI],     $csv[$id_habilite][INAOHabilitationCsvFile::CSV_PRODUIT_LIBELLE]),     ($csv[$id_habilite][INAOHabilitationCsvFile::CSV_VINIFICATEUR]),     "La ".$id_habilite."ème valeur est bien habilité");
$t->is($inao->isHabilite($csv[$id_habilite][INAOHabilitationCsvFile::CSV_CVI],     $csv[$id_habilite][INAOHabilitationCsvFile::CSV_PRODUIT_LIBELLE]." Dérivé"),     ($csv[$id_habilite][INAOHabilitationCsvFile::CSV_VINIFICATEUR]),     "La ".$id_habilite."ème valeur est bien habilité avec un dérivé du produit");
$t->is($inao->isHabilite($csv[$id_non_habilite][INAOHabilitationCsvFile::CSV_CVI], $csv[$id_non_habilite][INAOHabilitationCsvFile::CSV_PRODUIT_LIBELLE]), ($csv[$id_non_habilite][INAOHabilitationCsvFile::CSV_VINIFICATEUR]), "La ".$id_non_habilite."ème valeur est bien non habilité");
$t->is($inao->isHabilite($csv[$id_habilite][INAOHabilitationCsvFile::CSV_SIRET],     $csv[$id_habilite][INAOHabilitationCsvFile::CSV_PRODUIT_LIBELLE]),     ($csv[$id_habilite][INAOHabilitationCsvFile::CSV_VINIFICATEUR]),     "La ".$id_habilite."ème valeur fonctionne également avec le SIRET");
