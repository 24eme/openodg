<?php require_once(dirname(__FILE__).'/../bootstrap/common.php');

if (!DRevConfiguration::getInstance()->isModuleEnabled()) {
    $t = new lime_test();
    $t->pass("no DREV for ".$application);
    return;
}

$t = new lime_test(24);
$t->comment("test Import DR avec denomination automatique à ".DRevConfiguration::getInstance()->hasDenominationAuto());

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
//Suppression des DRev précédentes
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $drev = DRevClient::getInstance()->find($k);
    $drev->delete(false);
}

$t->comment("test avec le viti ".$viti->identifiant." (cvi:".$viti->cvi.")");

$periode = (date('Y')-1)."";

$config = ConfigurationClient::getCurrent();
$produit1 = null;
$produit2 = null;
foreach($config->getProduits() as $produit) {
    if(!$produit->getRendement()) {
        continue;
    }
    if(!$produit1) {
        $produit1 = $produit;
        continue;
    } elseif(!$produit2) {
        $produit2 = $produit;
        continue;
    }

    break;
}

$csvContentTemplate = file_get_contents(dirname(__FILE__).'/../data/dr_douane.csv');
$csvTmpFile = tempnam(sys_get_temp_dir(), 'openodg');
file_put_contents($csvTmpFile, str_replace(array("%cvi%", "%code_inao_1%", "%libelle_produit_1%","%code_inao_2%", "%libelle_produit_2%"), array("7523700100", $produit1->getCodeDouane(), $produit1->getLibelleComplet(), $produit2->getCodeDouane(), $produit2->getLibelleComplet()), $csvContentTemplate));
$csv = new DRDouaneCsvFile($csvTmpFile);
file_put_contents($csvTmpFile, $csv->convert());
$drCsv = new DRCsvFile($csvTmpFile);
$csvorig = $drCsv->getCsv();
$csv = $csvorig;
unlink($csvTmpFile);

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $periode);

if (DRevConfiguration::getInstance()->hasDenominationAuto()) {
  $drev->add('denomination_auto', DRevClient::DENOMINATION_BIO);
}
$drev->importCSVDouane($csv);
$drev->save();

$produits = array();
foreach ($csv as $line) {
    $key = $line[DouaneCsvFile::CSV_PRODUIT_INAO];
    if(DRevConfiguration::getInstance()->hasImportDRWithMentionsComplementaire()) {
        $key .= $line[DouaneCsvFile::CSV_PRODUIT_COMPLEMENT];
    }
    $produits[$key] = $key;
}

$t->comment("test sur ".$drev->_id);
$nb_produits_csv = count(array_keys($produits));
$t->is(count($drev->getProduits()), $nb_produits_csv, $nb_produits_csv." produits");
$drev->delete();

$t->comment("DREV avec que BIO de coché");
$drev->add('denomination_auto', DRevClient::DENOMINATION_BIO);
$t->is($drev->getDenominationAuto(), array(DRevClient::DENOMINATION_BIO), "On conserve la compatibilité avec le mode de déclaration des BIO_TOTAL déprécié");
$t->ok($drev->hasDenominationAuto(DRevClient::DENOMINATION_BIO), 'un déclaration de bio total provoque une appartenance au bio');
$t->ok(!$drev->hasDenominationAuto(DRevClient::DENOMINATION_CONVENTIONNEL), 'un déclaration de bio total ne provoque pas une appartenance au conventionnel');
$t->ok(!$drev->hasDenominationAuto(DRevClient::DENOMINATION_HVE), 'un déclaration de bio total ne provoque pas une appartenance au hve');

$t->comment("DREV avec que BIO et conventionnel de coché");
$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $periode);
$drev->add('denomination_auto', DRevClient::DENOMINATION_BIO_PARTIEL_DEPRECATED);
$t->is($drev->getDenominationAuto(), array(DRevClient::DENOMINATION_BIO, DRevClient::DENOMINATION_CONVENTIONNEL), "On conserve la compatibilité avec le mode de déclaration des BIO_PARTIEL déprécié");
$t->ok($drev->hasDenominationAuto(DRevClient::DENOMINATION_BIO), 'un déclaration de bio et conventionnel provoque une appartenance au bio');
$t->ok($drev->hasDenominationAuto(DRevClient::DENOMINATION_CONVENTIONNEL), 'un déclaration de bio et conventionnel provoque une appartenance au conventionnel');
$t->ok(!$drev->hasDenominationAuto(DRevClient::DENOMINATION_HVE), 'un déclaration de bio et conventionnel ne provoque pas une appartenance au hve');

//On met le premier produit en Bio
for($i = 0 ; $i < 15 ; $i++) {
    $csv[$i][DRCsvFile::CSV_PRODUIT_COMPLEMENT] = "vin bio";
    $csv[$i][DRCsvFile::CSV_LABEL_CALCULEE] = DRevClient::DENOMINATION_BIO;
}
if (DRevConfiguration::getInstance()->hasDenominationAuto()) {
  $drev->add('denomination_auto', array(DRevClient::DENOMINATION_BIO, DRevClient::DENOMINATION_CONVENTIONNEL));
}
$drev->importCSVDouane($csv);
$drev->save();
$nb = 0;
$ddetailfirst = null;
$ddetaillast = null;
foreach ($drev->declaration as $hash => $details) {
    foreach ($details as $d) {
        if (!$ddetailfirst) {
            $ddetailfirst = $d;
        }else{
            $ddetaillast = $d;
        }
        $nb += 1;
    }
}

if (DRevConfiguration::getInstance()->hasDenominationAuto()) {
    $t->is($nb, $nb_produits_csv, "bon nombre de produits ($nb_produits_csv) si les options automatique bio et conventionnelles sont activées et présence d'un bio en complement");
    $t->is($ddetailfirst->denomination_complementaire, DRevClient::DENOMINATION_BIO_LIBELLE_AUTO, "dénomination complémentaire si les options bio et conventionnelles");
} else {
    $t->is($nb, $nb_produits_csv - 1, "bon nombre de produits (3) si les options automatique bio et conventionnelles sont activées et présence d'un bio en complement");
    $t->is($ddetailfirst->denomination_complementaire, 'vin bio', "dénomination complémentaire de la dr");
}

$drev->delete();

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $periode);
if (DRevConfiguration::getInstance()->hasDenominationAuto()) {
  $drev->add('denomination_auto', DRevClient::DENOMINATION_BIO);
}
$drev->importCSVDouane($csv);
$drev->save();
$nb = 0;
$ddetailfirst = null;
$ddetaillast = null;
foreach ($drev->declaration as $hash => $details) {
    foreach ($details as $d) {
        if (!$ddetailfirst) {
            $ddetailfirst = $d;
        }else{
            $ddetaillast = $d;
        }
        $nb += 1;
    }
}
if (DRevConfiguration::getInstance()->hasDenominationAuto()) {
    $t->is($nb, $nb_produits_csv, "bon nombre de produits si seule l'option automatique bio est activée");
    $t->is($ddetailfirst->denomination_complementaire, DRevClient::DENOMINATION_BIO_LIBELLE_AUTO, "bonne dénomination de produit si seule l'option automatique bio est activée");
}else{
  $t->is($nb, $nb_produits_csv - 1, "bon nombre de produits si seule l'option automatique bio est activée");
  $t->is($ddetailfirst->denomination_complementaire, "vin bio", "bonne dénomination de produit si seule l'option automatique bio est activée");
}

$drev->delete();

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $periode);
if (DRevConfiguration::getInstance()->hasDenominationAuto()) {
  $drev->add('denomination_auto', array(DRevClient::DENOMINATION_BIO, DRevClient::DENOMINATION_CONVENTIONNEL));
}
$csv = $csvorig;
$drev->importCSVDouane($csv);
$drev->save();
$nb = 0;
$ddetailfirst = null;
$ddetaillast = null;
foreach ($drev->declaration as $hash => $details) {
    foreach ($details as $d) {
        if (!$ddetailfirst) {
            $ddetailfirst = $d;
        }else{
            $ddetaillast = $d;
        }
        $nb += 1;
    }
}
$nb_produits_csv_doublons = $nb_produits_csv;
if (DRevConfiguration::getInstance()->hasDenominationAuto() && $drev->hasDenominationAuto(DRevClient::DENOMINATION_BIO) && $drev->hasDenominationAuto(DRevClient::DENOMINATION_CONVENTIONNEL)) {
    $nb_produits_csv_doublons *= 2;
}
$t->is($nb, $nb_produits_csv_doublons, "bon nombre de produits ($nb_produits_csv_doublons) si les option bio et conventionnelles sont activées");
$t->isnt($ddetailfirst->denomination_complementaire, DRevClient::DENOMINATION_BIO_LIBELLE_AUTO, "dénomination complémentaire pour le produit non bio");
if (DRevConfiguration::getInstance()->hasDenominationAuto()) {
  $t->is($ddetaillast->denomination_complementaire, DRevClient::DENOMINATION_BIO_LIBELLE_AUTO, "dénomination complémentaire pour le produit bio");
}else{
  $t->is($ddetaillast->denomination_complementaire, "Melon", "dénomination complémentaire pour le produit bio");
}

$validation = new DRevValidation($drev);
$erreurs = $validation->getPointsByCodes('erreur');
$nb_bio = 0;
if (isset($erreurs['revendication_incomplete_volume']))
foreach($erreurs['revendication_incomplete_volume'] as $err) {
  if (preg_match('/ '.DRevClient::DENOMINATION_BIO_LIBELLE_AUTO.'/', $err->getInfo()) ) {
    $nb_bio++;
  }
}
$t->is($nb_bio, 0, "Pour les DRev avec bio et conventionnel pas de point blocant sur les produits bios non remplis");
$t->is(isset($erreurs['declaration_volume_l15_dr']), false, "Pour les DRev avec bio et conventionnel pas de point blocant sur le volume");
$t->is(isset($erreurs['revendication_superficie_dr']), false, "Pour les DRev avec bio et conventionnel pas de point blocant sur la superficie");

$drev->validate();
$drev->save();
$nb = 0;
foreach ($drev->declaration as $hash => $details) {
  $nb += count($details);
}
$t->is($nb, $nb_produits_csv, "bon nombre de produits si les options bio et conventionnelle sont activées et pas rempli les produits bio");

$drev->delete();
$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $periode);
if (DRevConfiguration::getInstance()->hasDenominationAuto()) {
  $drev->add('denomination_auto', array(DRevClient::DENOMINATION_BIO, DRevClient::DENOMINATION_CONVENTIONNEL, DRevClient::DENOMINATION_HVE));
}
$csv = $csvorig;
$drev->importCSVDouane($csv);
$drev->save();
$nb = 0;
foreach ($drev->declaration as $hash => $details) {
  $nb += count($details);
}
$nb_produits_csv_doublons = $nb_produits_csv;
if (DRevConfiguration::getInstance()->hasDenominationAuto() && $drev->hasDenominationAuto(DRevClient::DENOMINATION_BIO) && $drev->hasDenominationAuto(DRevClient::DENOMINATION_CONVENTIONNEL) && $drev->hasDenominationAuto(DRevClient::DENOMINATION_HVE)) {
    $nb_produits_csv_doublons *= 3;
}
$t->is($nb, $nb_produits_csv_doublons, "bon nombre de produits si les option bio, hve et conventionnelles sont activées");
$drev->delete();

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $periode);
if (DRevConfiguration::getInstance()->hasDenominationAuto()) {
  $drev->add('denomination_auto', array(DRevClient::DENOMINATION_BIO, DRevClient::DENOMINATION_CONVENTIONNEL, DRevClient::DENOMINATION_HVE));
}
$csv = $csvorig;
//On met le premier produit en HVE
for($i = 0 ; $i < 15 ; $i++) {
    $csv[$i][DRCsvFile::CSV_PRODUIT_COMPLEMENT] = "hve";
    $csv[$i][DRCsvFile::CSV_LABEL_CALCULEE] = DRevClient::DENOMINATION_HVE;
}
$drev->importCSVDouane($csv);
$drev->save();
$nb = 0;
$ddetailfirst = null;
$ddetaillast = null;
foreach ($drev->declaration as $hash => $details) {
    foreach($details as $d) {
        if (!$ddetailfirst) {
            $ddetailfirst = $d;
        }else{
            $ddetaillast = $d;
        }
        $nb += 1;
    }
}
$nb_produits_csv_doublons = $nb_produits_csv;
if (DRevConfiguration::getInstance()->hasDenominationAuto()) {
    $t->is($nb, 3, "Il y a 3 produits (HVE pour le premier et Bio et concventionnel pour le 2d)");
    $t->is($ddetailfirst->denomination_complementaire, DRevClient::DENOMINATION_HVE_LIBELLE_AUTO, "le 2d détail est HVE comme dans le CSV");
    $t->is($ddetaillast->denomination_complementaire, DRevClient::DENOMINATION_BIO_LIBELLE_AUTO, "le dernier détail est bio");
} else {
    $t->pass();
    $t->pass();
    $t->pass();
}
