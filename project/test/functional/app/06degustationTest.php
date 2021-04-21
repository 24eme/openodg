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
    $b = new sfTestFunctional(new Browser());
    $t = $b->test();
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

$b->click('button[id="lots_ajout"]')->followRedirect();
$b->click('button[id="lots_ajout"]')->followRedirect();
$b->with('response')->begin()->checkElement('.bloc-lot', 2)->end();
$b->click('button[id="lots_continue"]', array("drev_lots" => array("lots" => array(
    array("produit_hash" => $produit->getHash(), "volume" => 100),
    array("produit_hash" => $produit->getHash(), "volume" => 200),
))))->followRedirect()->followRedirect();
$b->isForwardedTo('drev', 'validation');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape validation");

$b->click('button[id="btn-validation-document-drev"]', array('validation' => array('date' => date('d/m/Y'))))->followRedirect();
$b->isForwardedTo('drev', 'visualisation');
$t->is($b->getResponse()->getStatuscode(), 200, "Page de confirmation");
$b->with('response')->begin()->checkElement('tr.hamzastyle-item', 2)->end();

$t->comment("Organisation d'une dégustation");

$b->get('/degustation');
$b->isForwardedTo('degustation', 'index');
$t->is($b->getResponse()->getStatuscode(), 200, "Page d'accueil des dégustations");

$b->click('button[type="submit"]', array('degustation_creation' => array('date' => date('d/m/Y'), 'time' => '11:00', 'max_lots' => '2')))->followRedirect();
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

$b->click('button[type="submit"]', array('preleve' => array('lots' => array(
    array("preleve" => "1"),
    array("preleve" => "1")
))))->followRedirect();
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

$b->back();
$b->click('#btn_suivant');
$b->isForwardedTo('degustation', 'tablesEtape');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape d'organisation des tables");

$b->click('#btn_organisation_table')->followRedirect();
$b->isForwardedTo('degustation', 'organisationTable');
$t->is($b->getResponse()->getStatuscode(), 200, "Formulaire d'organisation des tables");

$table1 = ['numero_lot_0' => 1, 'numero_lot_1' => 1];
for($i = 2; $i < 10; $i++) {
    $table1['numero_lot_'.$i] = 1;
    $b->click('#leurre_ajout', ['degustation_ajout_leurre' => ['table' => '1', 'hashref' => '/declaration/certifications/IGP/genres/TRANQ/appellations/MED/mentions/DEFAUT/lieux/DEFAUT/couleurs/rouge/cepages/DEFAUT']])->followRedirect();
}

$b->click('button[type="submit"]', array('tables' => $table1))->followRedirect();
$b->isForwardedTo('degustation', 'organisationTableRecap');
$t->is($b->getResponse()->getStatuscode(), 200, "Formulaire récapitulatif d'organisation des tables");

$b->click('button[type="submit"]')->followRedirect();
$b->isForwardedTo('degustation', 'tablesEtape');
$t->is($b->getResponse()->getStatuscode(), 200, "Retour à l'étape d'organisation des tables");

$b->click('#btn_suivant');
$b->isForwardedTo('degustation', 'anonymatsEtape');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape d'anonymat");

$b->click('#btn_suivant')->followRedirect();
$b->isForwardedTo('degustation', 'commissionEtape');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape commission");

$b->click('#btn_degustation_fiche_tables_echantillons_par_dossier_pdf');
$b->isForwardedTo('degustation', 'ficheTablesEchantillonsParDossierPDF');
$t->is($b->getResponse()->getContentType(), 'application/pdf', "Content type en pdf");
$t->is($b->getResponse()->getStatuscode(), 200, "PDF Fiche lots ventilés (Anonymisés)");

$b->back();
$b->click('#btn_pdf_degustation_fiche_tables_echantillons_par_anonymat_pdf');
$b->isForwardedTo('degustation', 'ficheTablesEchantillonsParAnonymatPDF');
$t->is($b->getResponse()->getContentType(), 'application/pdf', "Content type en pdf");
$t->is($b->getResponse()->getStatuscode(), 200, "PDF Fiche lots ventilés (Anonymisés par table)");

$b->back();
$b->click('#btn_pdf_degustation_etiquettes_tables_echantillons_par_anonymat_pdf');
$b->isForwardedTo('degustation', 'etiquettesTablesEchantillonsParAnonymatPDF');
$t->is($b->getResponse()->getContentType(), 'application/pdf', "Content type en pdf");
$t->is($b->getResponse()->getStatuscode(), 200, "PDF Tableau des étiquettes (Anonymisés)");

$b->back();
$b->click('#btn_pdf_presence_degustateurs');
$b->isForwardedTo('degustation', 'fichePresenceDegustateursPDF');
$t->is($b->getResponse()->getContentType(), 'application/pdf', "Content type en pdf");
$t->is($b->getResponse()->getStatuscode(), 200, "Feuille de présence des dégustateurs");

$b->back();
$b->click('#btn_pdf_fiche_individuelle_degustateurs');
$b->isForwardedTo('degustation', 'ficheIndividuellePDF');
$t->is($b->getResponse()->getContentType(), 'application/pdf', "Content type en pdf");
$t->is($b->getResponse()->getStatuscode(), 200, "Fiche individuelle des dégustateurs");

$b->back();
$b->click('#btn_pdf_fiche_individuelle_degustateurs', ['output' => 'html']);
$b->isForwardedTo('degustation', 'ficheIndividuellePDF');
$t->is($b->getResponse()->getContentType(), 'text/html; charset=utf-8', "Content type en html");
$t->is($b->getResponse()->getStatuscode(), 200, "PDF Fiche individuelle dégustateur html");

$b->with('response')->begin()->
    checkElement('table#table_fiche_1 tr:nth-child(3) td:nth-child(1) strong', "A01")->
    checkElement('table#table_fiche_1 tr:nth-child(4) td:nth-child(1) strong', "A02")->
    checkElement('table#table_fiche_1 tr:nth-child(5) td:nth-child(1) strong', "A03")->
    checkElement('table#table_fiche_1 tr:nth-child(6) td:nth-child(1) strong', "A04")->
    checkElement('table#table_fiche_1 tr:nth-child(7) td:nth-child(1) strong', "A05")->
    checkElement('table#table_fiche_1 tr:nth-child(8) td:nth-child(1) strong', "A06")->
    checkElement('table#table_fiche_1 tr:nth-child(9) td:nth-child(1) strong', "A07")->
    checkElement('table#table_fiche_1 tr:nth-child(10) td:nth-child(1) strong', "A08")->
    checkElement('table#table_fiche_2 tr:nth-child(3) td:nth-child(1) strong', "A09")->
    checkElement('table#table_fiche_2 tr:nth-child(4) td:nth-child(1) strong', "A10")
->end();

$b->back();
$b->click('#btn_pdf_fiche_resultats_table');
$b->isForwardedTo('degustation', 'ficheRecapTablesPDF');
$t->is($b->getResponse()->getContentType(), 'application/pdf', "Content type en pdf");
$t->is($b->getResponse()->getStatuscode(), 200, "Fiche individuelle des dégustateurs");

$b->back();
$b->click('#btn_suivant');
$b->isForwardedTo('degustation', 'resultatsEtape');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape résultats");

$b->click('#btn_resultats')->followRedirect();
$b->isForwardedTo('degustation', 'resultats');
$t->is($b->getResponse()->getStatuscode(), 200, "Formulaire des résultats");

$b->click('#popupResultat_0 button[type="submit"]')->followRedirect();
$b->isForwardedTo('degustation', 'resultats');
$t->is($b->getResponse()->getStatuscode(), 200, "Validation du formulaire de résultat d'un échantillon");

$b->click('button[type="submit"]')->followRedirect();
$b->isForwardedTo('degustation', 'resultatsEtape');
$t->is($b->getResponse()->getStatuscode(), 200, "Retour à l'étape de résultats");

$b->click('#btn_suivant');
$b->isForwardedTo('degustation', 'notificationsEtape');
$t->is($b->getResponse()->getStatuscode(), 200, "Étape notifications");

$b->click('#btn_pdf_fiches_proces_verbal');
$b->isForwardedTo('degustation', 'procesVerbalDegustationPDF');
$t->is($b->getResponse()->getContentType(), 'application/pdf', "Content type en pdf");
$t->is($b->getResponse()->getStatuscode(), 200, "PDF Fiche de procès verbal");

$b->back();
$b->click('#btn_pdf_notifications');
$b->isForwardedTo('degustation', 'degustationAllNotificationsPDF');
$t->is($b->getResponse()->getContentType(), 'application/pdf', "Content type en pdf");
$t->is($b->getResponse()->getStatuscode(), 200, "PDF de toutes les notifications");

$b->back();
$b->isForwardedTo('degustation', 'notificationsEtape');
$t->is($b->getResponse()->getStatuscode(), 200, "Retour à l'étape de notifications");

$b->click(".btn-mail-previsualisation");
$b->isForwardedTo('degustation', 'mailPrevisualisation');
$t->is($b->getResponse()->getStatuscode(), 200, "Popup de prévisualisation");

$b->click("#pdf_lots_conforme");
$b->isForwardedTo('degustation', 'degustationConformitePDF');
$t->is($b->getResponse()->getContentType(), 'application/pdf', "Content type en pdf");
$t->is($b->getResponse()->getStatuscode(), 200, "PDF de conformités");

$uriConformiteProtege = $b->getRequest()->getUri();

$b->back();
$b->click("pre a");
$b->isForwardedTo('degustation', 'getCourrierWithAuth');
$t->is($b->getResponse()->getContentType(), 'application/pdf', "Content type en pdf");
$t->is($b->getResponse()->getStatuscode(), 200, "PDF de conformités (par url authentifiante)");

$uriConformiteAuthentifiante = $b->getRequest()->getUri();

$t->comment('En mode télédéclarant');

$b->get('/logout');
$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_CAS', 'app_auth_rights' => array()));
$b->restart();

$b->post('/login_no_cas', array('admin' => array('login' => $societe->getIdentifiant())));
$t->is($b->getResponse()->getStatuscode(), 302, "Login réussi");

$b->get('/degustation');
$t->is($b->getResponse()->getStatuscode(), 403, "Accueil des dégustations interdite");

$t->comment('En mode non connecté');

$b->get('/logout');
$b->setAdditionnalsConfig(array('app_auth_mode' => 'NO_CAS', 'app_auth_rights' => array()));
$b->restart();

$b->get('/degustation');
$b->isForwardedTo('auth', 'login');
$t->is($b->getResponse()->getStatuscode(), 200, "Redirection sur la page de login");

$b->get($uriConformiteProtege);
$b->isForwardedTo('auth', 'login');
$t->is($b->getResponse()->getStatuscode(), 200, "Le PDF de conformités par url classique est protégé");

$b->get($uriConformiteAuthentifiante);
$b->isForwardedTo('degustation', 'getCourrierWithAuth');
$t->is($b->getResponse()->getContentType(), 'application/pdf', "Content type en pdf");
$t->is($b->getResponse()->getStatuscode(), 200, "Le PDF de conformités par url authentifiant est accessible");
