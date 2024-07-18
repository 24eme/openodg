<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if (in_array($application, array('nantes', 'loire'))) {
    $t = new lime_test(1);
    $t->ok(true, "pas de parcellaire activé");
    return;
}

$t = new lime_test();
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
$year = date('Y') - 1;
$date = $year.'-12-01';
$campagne = $year.'-'.($year + 1);
foreach(ParcellaireClient::getInstance()->getHistory($viti->identifiant, ParcellaireClient::TYPE_COUCHDB, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $parcellaire = ParcellaireClient::getInstance()->find($k);
    $parcellaire->delete(false);
}

$parcellaire = ParcellaireClient::getInstance()->findOrCreate($viti->identifiant, $date, "INAO");
$parcellaire->save();

$t->is($parcellaire->_id, 'PARCELLAIRE-'.$viti->identifiant.'-'.str_replace("-", "", $date), "L'id du doc est ".'PARCELLAIRE-'.$viti->identifiant.'-'.str_replace("-", "", $date));
$t->is($parcellaire->campagne, $campagne, 'La campagne du parcellaire est bien indiquée');
$t->is($parcellaire->source, "INAO", "La source des données est l'INAO");

$configProduit = null;
foreach($parcellaire->getConfigProduits() as $produit) {
    $configProduit = $produit;
    break;
}

$communes = CommunesConfiguration::getInstance()->getByCodeCommune();
$t->ok($communes, "config/communes.yml contient des communes");
$commune = current($communes);
$code_commune = key($communes);
$commune2 = next($communes);
$numero_ordre_key = "00";
$parcelle = $parcellaire->addParcelleWithProduit($configProduit->getHash(), $configProduit->getLibelleComplet(), "Sirah N", "2005", $commune, "", "AB", "52", "LA HAUT");
$parcellaire->addParcelleWithProduit($configProduit->getHash(), $configProduit->getLibelleComplet(), "Grenache", "2010", $commune2, "", "AK", "47", null);
$parcellaire->addParcelleWithProduit($configProduit->getHash(), $configProduit->getLibelleComplet(), "Sirah N", "2005", $commune, "", "AB", "52", "LA HAUT",25);
$p = $parcellaire->addParcelle($code_commune.'000AB0052', $configProduit->getLibelleComplet(), "Sirah N", "2005", $commune, "LA HAUT");
$new_parcelle = $parcellaire->affecteParcelleToHashProduit($configProduit->getHash(), $p);
$p = $parcellaire->addParcelle($code_commune.'000AB0055', "VSIG", "Sirah N", "2005", $commune, "LA HAUT");

$parcellaire->save();

$t->is(count($parcellaire->declaration), 1, "Le parcellaire a un produit");
$t->is(count($parcellaire->getParcelles()), 5, "Le parcellaire 4 parcelles");
$t->is(count($parcellaire->declaration->getParcelles()), 4, "Le parcellaire a des parcelles dans le produit");
$parcelle = array_values($parcellaire->declaration->getParcelles())[0];
$t->is($parcelle->produit_hash, $configProduit->getHash(), "La première parcelles du produit as bien un produit_hash");
$t->is($parcelle->getConfig()->getLibelleComplet(), $configProduit->getLibelleComplet(), "Le libellé du produit est ". $configProduit->getLibelleComplet());
$t->is($parcelle->source_produit_libelle, $configProduit->getLibelleComplet(), "Le libellé source du produit est ". $configProduit->getLibelleComplet());
$t->is($parcelle->getKey(), $code_commune."000AB0052-00", "La clé de la parcelle est bien construite");
$t->is($parcelle->code_commune, $code_commune, "Le code commune est : $code_commune");
$t->is($parcelle->campagne_plantation, "2005", "La campagne de plantation a été enregistré");
$t->is($parcelle->cepage, "Sirah N", "Le cépage a été enregistré");
$t->is($parcelle->numero_ordre, 0, "Le numéro d'ordre a été enregistré");
$t->is($parcelle->commune, $commune, "La commune est : " . $commune);
$t->is($parcelle->lieu, "LA HAUT", "La lieu est : LA HAUT");
$t->is($parcelle->idu, $code_commune."000AB0052" , "Le code IDU est ".$code_commune."000AB0052");
$t->is($parcelle->isRealProduit(), true , "Le produit est un produit géré");

$parcelles = $parcellaire->getParcelles()->toArray();
array_shift($parcelles);
array_shift($parcelles);
$parcelle3 = array_shift($parcelles);
$t->is($parcelle3->getKey(), $code_commune."000AB0052-01", "La clé de la parcelle 3 est bien construite");
$t->is($parcelle3->produit_hash, '/declaration/certifications/AOC/genres/TRANQ/appellations/VTX/mentions/DEFAUT/lieux/DEFAUT/couleurs/rouge/cepages/DEFAUT', "la parcelle provenant de parcelle a bien une bonne hash produit");

$parcelle4 = array_shift($parcelles);
$t->is($parcelle4->getKey(), $code_commune."000AB0052-02", "La clé de la parcelle 4 est bien construite : elle a pour numéro d'ordre '26'");

$t->is($parcellaire->pieces[0]->libelle, "Parcellaire au ".$parcellaire->getDateFr(), "La déclaration a bien généré un document (une pièce)");

$t->comment("import de cvs parcellaire (mimique le résultat du scrapping)");

$csv_path = "/tmp/parcellaire-".$viti->cvi.".csv";
$csv_file = fopen($csv_path, 'w');
fwrite($csv_file, "CVI Operateur;Siret Operateur;Nom Operateur;Adresse Operateur;CP Operateur;Commune Operateur;Email Operateur;IDU;Commune;Lieu dit;Section;Numero parcelle;Produit;Cepage;Superficie;Superficie cadastrale;Campagne;Ecart pied;Ecart rang;Mode savoir faire;Statut;Date MaJ\n");
fwrite($csv_file , $viti->cvi.";".$viti->siret.";".$viti->raison_sociale.";;".$code_commune.";".$commune.";;".$code_commune."000AM0049;".$commune.";CROSAN;AM;49;".$configProduit->getLibelleComplet().";GRENACHE N;0.2903;0.797;1968-1969;130;225;;Fermier;\n");
fwrite($csv_file , $viti->cvi.";".$viti->siret.";".$viti->raison_sociale.";;".$code_commune.";".$commune.";;".$code_commune."000AM0049;".$commune.";CROSAN;AM;49;".$configProduit->getLibelleComplet().";GRENACHE N;0.182;0.797;1977-1978;130;225;;Fermier;\n");
fwrite($csv_file , $viti->cvi.";".$viti->siret.";".$viti->raison_sociale.";;".$code_commune.";".$commune.";;".$code_commune."000AM0049;".$commune.";CROSAN;AM;49;".$configProduit->getLibelleComplet().";CARIGNAN N;0.214;0.797;2014-2015;140;225;;Fermier;\n");
fwrite($csv_file , $viti->cvi.";".$viti->siret.";".$viti->raison_sociale.";;".$code_commune.";".$commune.";;".$code_commune."000AM0049;".$commune.";CROSAN;AM;49;".$configProduit->getLibelleComplet().";GRENACHE N;0.1107;0.797;2014-2015;130;225;;Fermier;\n");
fwrite($csv_file , $viti->cvi.";".$viti->siret.";".$viti->raison_sociale.";;".$code_commune.";".$commune.";;".$code_commune."0000C0027;".$commune.";BARE;C;27;".$configProduit->getLibelleComplet().";SYRAH N;0.466;0.466;1977-1978;130;225;;Fermier;\n");
fwrite($csv_file , $viti->cvi.";".$viti->siret.";".$viti->raison_sociale.";;".$code_commune.";".$commune.";;".$code_commune."000AB0009;".$commune.";VERSAN;AB;9;;LIVAL N;0.3;;1980-1981;130;225;;Fermier;\n");
fclose($csv_file);

$parcellaire = ParcellaireClient::getInstance()->findOrCreate(
    $viti->identifiant,
    date('Y-m-d'),
    'PRODOUANE'
);
$import = new ParcellaireCsvFile($parcellaire, $csv_path);
$import->convert();
$import->save();
$parcellaire = $import->getParcellaire();
$parcelles = $parcellaire->getParcelles();
$t->is(count($parcelles), 6, "L'import permet bien d'avoir 6 parcelles dans le noeuds parcelles");
$t->is($parcelles[$code_commune.'000AM0049-00']->produit_hash, $configProduit->getHash(), "La première parcelle a le bon produit (".$configProduit->getHash().")");
$t->ok($parcelles[$code_commune.'000AM0049-00']->getParcelleAffectee(), "On trouve la première parcelle dans le noeud déclaration");
$t->is($parcelles[$code_commune.'000AM0049-00']->isRealProduit(), true, "La première parcelle est un produit géré");
$t->is(count($parcellaire->getDeclarationParcelles()), 5, "Il y a 5 parcelles dans les produits gérés");
$parcellaire->save();
