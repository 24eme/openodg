<?php

include dirname(__FILE__).'/../../bootstrap/functional.php';

$etablissement = CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_functionnal_etablissement')->getEtablissement();
$societe = $etablissement->getSociete();

$application = getenv('APPLICATION');

$has_etape_lot = false;
$has_produit_lot = false;
$has_vci = true;
$has_aoc = true;

if ($application != 'igp13') {
    return;
}

foreach(DRevClient::getInstance()->getHistory($etablissement->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $drev = DRevClient::getInstance()->find($k);
    $drev->delete(false);
}

foreach(PieceAllView::getInstance()->getPiecesByEtablissement($etablissement->identifiant, true) as $piece) {
    if(strpos($piece->id, 'DR-') === false) {
        continue;
    }

    $fichier = FichierClient::getInstance()->find($piece->id);
    $fichier->delete();
}

foreach(DegustationClient::getInstance()->getHistory(999, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $degustation = DegustationClient::getInstance()->find($k);
    $degustation->delete();
}

$config = ConfigurationClient::getCurrent();
$produit = null;
foreach($config->getProduits() as $p) {
    if($p->getRendement() <= 0) {
        continue;
    }
    if(!$produit) {
        $produit = $p;
        continue;
    }

    break;
}

$b = new sfTestFunctional(new Browser());
$t = $b->test();

$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_AUTH', 'app_auth_rights' => null));

$t->comment("Saisie d'une DRev");

$b->get('/declarations/'.$etablissement->identifiant);
$b->isForwardedTo('declaration', 'etablissement');
$t->is($b->getResponse()->getStatuscode(), 200, "Page declaration");

$b->click('a[href*="/drev/creation-papier/"]')->followRedirect()->followRedirect();
$b->isForwardedTo('drev', 'exploitation');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape exploitation");

$b->click('button[type="submit"]', array('import_dr_prodouane' => false))->followRedirect()->followRedirect()->followRedirect();
$b->isForwardedTo('drev', 'lots');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape lot");

$b->click('button[id="lots_continue"]')->followRedirect()->followRedirect();
$b->isForwardedTo('drev', 'validation');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape validation");

$b->click('button[id="btn-validation-document-drev"]', array('validation' => array('date' => date('d/m/Y'))))->followRedirect();
$b->isForwardedTo('drev', 'visualisation');
$t->is($b->getResponse()->getStatuscode(), 200, "Page de confirmation");

$t->comment("Organisation d'une dégustation");

$b->get('/degustation');
$b->isForwardedTo('degustation', 'index');
$t->is($b->getResponse()->getStatuscode(), 200, "Page d'accueil des dégustations");

$b->click('button[type="submit"]', array('degustation_creation' => array('date' => date('d/m/Y'), 'time' => '11:00', 'max_lots' => '1')))->followRedirect();
$b->isForwardedTo('degustation', 'prelevementLots');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape de séléction des lots");

$b->click('button[type="submit"]')->followRedirect()->followRedirect();
$b->isForwardedTo('degustation', 'selectionDegustateurs');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape de séléction des dégustateurs - Technicien");

$b->click('button[type="submit"]')->followRedirect();
$b->isForwardedTo('degustation', 'selectionDegustateurs');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape de séléction des dégustateurs - Porteur de mémoire");

$b->click('button[type="submit"]')->followRedirect();
$b->isForwardedTo('degustation', 'selectionDegustateurs');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape de séléction des dégustateurs - Usager du produit");

$b->click('button[type="submit"]')->followRedirect();
$b->isForwardedTo('degustation', 'convocations');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape de convocation des dégustateurs");

$b->click('#btn_suivant');
$b->isForwardedTo('degustation', 'prelevementsEtape');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape de prélèvements");

$b->click('#btn_suivi_prelevement');
$b->isForwardedTo('degustation', 'preleve');
$t->is($b->getResponse()->getStatuscode(), 200, "Saisie des prélévements");

$b->click('button[type="submit"]', array('preleve' => array('lots' => array(array("preleve" => "1")))))->followRedirect();
$b->isForwardedTo('degustation', 'prelevementsEtape');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape de prélevements");

$b->click('#btn_pdf_fiche_tournee_prelevement');
$b->isForwardedTo('degustation', 'ficheLotsAPreleverPDF');
$t->is($b->getResponse()->getContentType(), 'application/pdf', "Content type en pdf");
$t->is($b->getResponse()->getStatuscode(), 200, "PDF Fiche de tournée de prélevement");

$b->back();
$b->click('#btn_pdf_fiche_individuelle_lots_a_prelever');
$b->isForwardedTo('degustation', 'ficheIndividuelleLotsAPreleverPDF');
$t->is($b->getResponse()->getContentType(), 'application/pdf', "Content type en pdf");
$t->is($b->getResponse()->getStatuscode(), 200, "PDF Fiche de tournée de prélevement");

$b->back();
$b->click('#btn_pdf_etiquettes_de_prelevement');
$b->isForwardedTo('degustation', 'etiquettesPrlvmtPdf');
$t->is($b->getResponse()->getContentType(), 'application/pdf', "Content type en pdf");
$t->is($b->getResponse()->getStatuscode(), 200, "PDF Étiquettes de prélevement");

$b->back();
$b->click('#btn_csv_etiquette');
$b->isForwardedTo('degustation', 'etiquettesPrlvmtCsv');
$t->is($b->getResponse()->getContentType(), 'text/csv; charset=ISO-8859-1', "Content type en csv");
$t->is($b->getResponse()->getStatuscode(), 200, "CSV Étiquettes de prélevement");


