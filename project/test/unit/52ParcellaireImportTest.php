<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if (in_array($application, array('nantes', 'loire'))) {
    $t = new lime_test(1);
    $t->ok(true, "pas de parcellaire activé");
    return;
}

$toutes_les_parcelles = !ParcellaireConfiguration::getInstance()->getLimitProduitsConfiguration();

$t = new lime_test(28 + $toutes_les_parcelles * 2);
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
$date = date('Y-m-d');

foreach(ParcellaireClient::getInstance()->getHistory($viti->identifiant, ParcellaireClient::TYPE_COUCHDB, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $parcellaire = ParcellaireClient::getInstance()->find($k);
    $parcellaire->delete(false);
}

$configProduit = [];
foreach (ConfigurationClient::getCurrent()->getProduits() as $produit) {
    $configProduit[] = $produit;
}

$communes = CommunesConfiguration::getInstance()->getByCodeCommune();
$commune = current($communes);
$code_commune = key($communes);
$numero_ordre_key = "00";

$array = [
    [$viti->cvi, $viti->siret, $viti->nom, $viti->adresse, $viti->code_postal, $viti->commune, 'email@exemple.com', $code_commune.'0000AY0036', "$commune",'SAINT-OUEN','AY','36', $configProduit[0]->getLibelleFormat(),'SYRAH N','0.1', '0.7', '2017-2018','100','250', '', 'Propriétaire'],
    [$viti->cvi, $viti->siret, $viti->nom, $viti->adresse, $viti->code_postal, $viti->commune, 'email@exemple.com', $code_commune.'0000AY0037', "$commune",'SAINT-OUEN','AY','37', $configProduit[0]->getLibelleFormat(),'GRENACHE N','0.6', '0.7', '2006-2007','100','250', '', 'Propriétaire'],
    [$viti->cvi, $viti->siret, $viti->nom, $viti->adresse, $viti->code_postal, $viti->commune, 'email@exemple.com', '750630000AM0152', 'PARIS','MARSEILLE','AM','152', $configProduit[1]->getLibelleFormat(),'GRENACHE N','1.1', '1.1', '2001-2002','100','250', '', 'Fermier'],
    [$viti->cvi, $viti->siret, $viti->nom, $viti->adresse, $viti->code_postal, $viti->commune, 'email@exemple.com', '750630000AM0052', 'PARIS','MARSEILLE','AL','52', '','SYRAH N','1.1', '1.1', '2001-2002','100','250', '', 'Fermier']
];

$tempfname = tempnam('/tmp', "PARCELLAIRE-$viti->cvi-".date('Ymd', strtotime("-7 day"))."-");
$handle = fopen($tempfname, 'w');
foreach ($array as $line) {
    fputcsv($handle, $line, ';');
}
fclose($handle);

$t->comment("import $tempfname ");

$csv_test = new Csv($tempfname, ';', false);
$parcellaireloader = new ParcellaireCsvFile($viti, $csv_test);
$parcellaireloader->convert();
$parcellaireloader->save();

$parcellaire = $parcellaireloader->getParcellaire();

$parcellaire_id = 'PARCELLAIRE-'.$viti->identifiant.'-'.str_replace("-", "", $date);
$t->is($parcellaire->_id, $parcellaire_id, "L'id du doc est $parcellaire_id");
$t->is($parcellaire->source, "PRODOUANE", "La source des données est PRODOUANE");
$t->is(count($parcellaire->declaration), ($toutes_les_parcelles) ? 3 : 2, "Le parcellaire a le bon nombre de produits");

$parcelles = $parcellaire->getParcelles();


$t->is(count($parcelles), ($toutes_les_parcelles) ? 4 : 3, "Le parcellaire contient les bonnes parcelles");

$parcelle = array_shift($parcelles);

$t->is($parcelle->getProduit()->getLibelle(), $configProduit[0]->getLibelleComplet(), "Le libellé du produit est ". $configProduit[0]->getLibelleComplet());
$t->is($parcelle->getKey(), "SYRAH-N-2017-2018-".$commune."-AY-36-".$numero_ordre_key."-SAINT-OUEN", "La clé de la parcelle est bien construite");
$t->is($parcelle->code_commune, $code_commune, "Le code commune est : $code_commune");
$t->is($parcelle->campagne_plantation, "2017-2018", "La campagne de plantation a été enregistré");
$t->is($parcelle->cepage, "SYRAH N", "Le cépage a été enregistré");
$t->is($parcelle->numero_ordre, 0, "Le numéro d'ordre a été enregistré");
$t->is($parcelle->commune, $commune, "La commune est : " . $commune);
$t->is($parcelle->lieu, "SAINT-OUEN", "La lieu est : SAINT-OUEN");
$t->is($parcelle->idu, $code_commune."000AY0036" , "Le code IDU est ".$code_commune."000AY0036");

array_shift($parcelles);
$parcelle3 = array_shift($parcelles);
$t->is($parcelle3->getKey(), "GRENACHE-N-2001-2002-PARIS-AM-152-00-MARSEILLE", "La clé de la parcelle 3 est bien construite");
$t->is($parcelle3->getProduit()->getLibelle(), $configProduit[1]->getLibelleComplet(), "Le libelle du produit est " . $configProduit[1]->getLibelleComplet());

$t->is($parcellaire->pieces[0]->libelle, "Parcellaire au ".$parcellaire->getDateFr(), "La déclaration a bien généré un document (une pièce)");

if ($toutes_les_parcelles) {
    $parcelle_sans_produit = array_shift($parcelles);
    $t->is($parcelle_sans_produit->getKey(), "SYRAH-N-2001-2002-PARIS-AL-52-00-MARSEILLE", "La clé de la parcelle sans produite est bien construite");
    $t->is($parcelle_sans_produit->getProduit()->getLibelle(), ParcellaireClient::PARCELLAIRE_DEFAUT_PRODUIT_LIBELLE, "Le libelle du produit est celui de l'absence de produit");
}

$t->comment("vérification de la synthèse produits");

$synthese = $parcellaire->getSyntheseProduitsCepages();
if ($toutes_les_parcelles) {
    $t->is(count(array_keys($synthese)), 1, "La synthese produits donne les produits issus de l'habilitation");
}else{
    $t->is(count(array_keys($synthese)), 2, "La synthese produits a le bon nombre de produits");
}
$synthese_produit_1_key = array_shift(array_keys($synthese));
$t->is(count(array_keys($synthese[$synthese_produit_1_key])), 3, "La synthese du premier produit a deux cépages + un total");
if ($toutes_les_parcelles) {
    $t->is($synthese[$synthese_produit_1_key]['Total']['superficie_max'], 2.9, "La synthese du premier produit pour tous les cépages a la bonne superficie");
}else{
    $t->is($synthese[$synthese_produit_1_key]['Total']['superficie_max'], 0.7, "La synthese du premier produit pour tous les cépages a la bonne superficie");
}

$synthese = $parcellaire->getSyntheseCepages();
$t->is(count(array_keys($synthese)), 2, "La synthese produits a le bon nombre de cepages");
$synthese_cepage_1_key = array_shift(array_keys($synthese));

$t->is(array_keys($synthese), ['GRENACHE N', 'SYRAH N'], "La synthèse est triée par cépage");
$t->is($synthese[$synthese_cepage_1_key]['superficie'], ($toutes_les_parcelles) ? 1.7 : 0.6, "La synthese cepage du premier cépage (".$synthese_cepage_1_key.") a la bonne superficie");



$t->comment("import d'un fichier avec une parcelle en moins $tempfname ");

$array = [
    [$viti->cvi, $viti->siret, $viti->nom, $viti->adresse, $viti->code_postal, $viti->commune, 'email@exemple.com', $code_commune.'0000AY0036', "$commune",'SAINT-OUEN','AY','36', $configProduit[0]->getLibelleFormat(),'GRENACHE N','0.1', '0.7', '2017-2018','100','250', '', 'Propriétaire'],
    [$viti->cvi, $viti->siret, $viti->nom, $viti->adresse, $viti->code_postal, $viti->commune, 'email@exemple.com', $code_commune.'0000AY0037', "$commune",'SAINT-OUEN','AY','37', $configProduit[0]->getLibelleFormat(),'GRENACHE N','0.6', '0.7', '2006-2007','100','250', '', 'Propriétaire'],
];
unlink($tempfname);
$tempfname = tempnam('/tmp', "PARCELLAIRE-$viti->cvi-".date('Ymd', strtotime("-6 day"))."-");
$handle = fopen($tempfname, 'w');
foreach ($array as $line) {
    fputcsv($handle, $line, ';');
}
fclose($handle);


$csv_test = new Csv($tempfname, ';', false);
$parcellaireloader = new ParcellaireCsvFile($viti, $csv_test);
$parcellaireloader->convert();
$parcellaireloader->save();

$parcellaire = $parcellaireloader->getParcellaire();

$parcellaire_id = 'PARCELLAIRE-'.$viti->identifiant.'-'.str_replace("-", "", $date);
$t->is($parcellaire->_id, $parcellaire_id, "L'id du doc est $parcellaire_id");
$t->is(count($parcellaire->declaration), 1, "Le nouveau parcellaire n'a qu'un seul produit");

$parcelles = $parcellaire->getParcelles();
$t->is(count($parcelles), 2, "Le nouveau parcellaire a deux parcelles");

$t->comment("import d'un fichier sans parcelles $tempfname ");
unlink($tempfname);
$tempfname = tempnam('/tmp', "PARCELLAIRE-$viti->cvi-".date('Ymd', strtotime("-5 day"))."-");
$handle = fopen($tempfname, 'w');
fwrite($handle, "CVI Operateur;Siret Operateur;Nom Operateur;Adresse Operateur;CP Operateur;Commune Operateur;Email Operateur;Commune;Lieu dit;Section;Numero parcelle;Produit;Cepage;Superficie;Superficie cadastrale;Campagne;Ecart pied;Ecart rang;Mode savoir faire;Statut");
fclose($handle);

$csv_test = new Csv($tempfname, ';', false);
$parcellaireloader = new ParcellaireCsvFile($viti, $csv_test);
$parcellaireloader->convert();
$parcellaireloader->save();
$parcellaire = $parcellaireloader->getParcellaire();
$t->is($parcellaire->_id, $parcellaire_id, "L'id du doc est $parcellaire_id");
$t->is(count($parcellaire->declaration), 0, "Le nouveau parcellaire n'a pas de produit");

$t->comment("import des parcelles ayant la même clé");
$csv_same_parcelles = tempnam('/tmp', "PARCELLAIRE-$viti->cvi-".date('Ymd', strtotime("-10 day"))."-");
$handle = fopen($csv_same_parcelles, "w");
fputcsv($handle, explode(";", "CVI Operateur;Siret Operateur;Nom Operateur;Adresse Operateur;CP Operateur;Commune Operateur;Email Operateur;Commune;Lieu dit;Section;Numero parcelle;Produit;Cepage;Superficie;Superficie cadastrale;Campagne;Ecart pied;Ecart rang;Mode savoir faire;Statut"));
$array = [
    [$viti->cvi, $viti->siret, $viti->nom, $viti->adresse, $viti->code_postal, $viti->commune, 'email@exemple.com', $code_commune.'0000AY0036', "$commune",'SAINT-OUEN','AY','36', $configProduit[0]->getLibelleFormat(),'GRENACHE N','0.1', '0.7', '2017-2018','100','250', '', 'Propriétaire'],
    [$viti->cvi, $viti->siret, $viti->nom, $viti->adresse, $viti->code_postal, $viti->commune, 'email@exemple.com', $code_commune.'0000AY0036', "$commune",'SAINT-OUEN','AY','36', $configProduit[0]->getLibelleFormat(),'GRENACHE N','0.6', '0.7', '2017-2018','100','250', '', 'Propriétaire'],
];
$array[] = explode(";", "7523700100;33223322332233;Gaec de l'etablissement;7 Lieu-dit;49310;NEUILLY;a@b.com;492110000C1359;VILLARS-SUR-VAR;Mauvais Patis;C;1359;VAL LOIRE blanc;SAUVIGNON B;0.4409;0.819;2016-2017;100;185;;Fermier");
$array[] = explode(";", "7523700100;33223322332233;Gaec de l'etablissement;7 Lieu-dit;49310;NEUILLY;a@b.com;492110000C1359;VILLARS-SUR-VAR;Mauvais Patis;C;1359;VAL LOIRE blanc;SAUVIGNON B;0.3781;0.819;2016-2017;100;185;;Fermier");

foreach ($array as $l) {
    fputcsv($handle, $l, ";");
}
fclose($handle);

$csv = new Csv($csv_same_parcelles, ';');
$parcellaireLoader = new ParcellaireCsvFile($viti, $csv);
$parcellaireLoader->convert();

$parcellaire = $parcellaireLoader->getParcellaire();
$t->is(count($parcellaire->getParcelles()), 4, "Il y a quatre parcelles");
unlink($tempfname);
