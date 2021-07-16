<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Pass AOC");
    return;
}

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
$societe = $viti->getSociete();
$socVitiCompte = $societe->getMasterCompte();
//Suppression des DRev précédentes
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    DRevClient::getInstance()->deleteDoc(DRevClient::getInstance()->find($k, acCouchdbClient::HYDRATE_JSON));
    $dr = DRClient::getInstance()->find(str_replace("DREV-", "DR-", $k), acCouchdbClient::HYDRATE_JSON);
    if($dr) { DRClient::getInstance()->deleteDoc($dr); }
    $sv12 = SV12Client::getInstance()->find(str_replace("DREV-", "SV12-", $k), acCouchdbClient::HYDRATE_JSON);
    if($sv12) { SV12Client::getInstance()->deleteDoc($sv12); }
    $sv11 = SV11Client::getInstance()->find(str_replace("DREV-", "SV11-", $k), acCouchdbClient::HYDRATE_JSON);
    if($sv11) { SV11Client::getInstance()->deleteDoc($sv11); }
}



$oldmandat = MandatSepaClient::getInstance()->findLastBySociete($societe->identifiant);
if ($oldmandat) {
    MandatSepaClient::getInstance()->delete($oldmandat);
}
foreach(FactureClient::getInstance()->getFacturesByCompte($socVitiCompte->identifiant) as $k => $f) {
    FactureClient::getInstance()->delete($f);
}


$t = new lime_test(38);

$t->is(MandatSepaConfiguration::getInstance()->getMentionAutorisation(), 'En signant ce formulaire de mandat, vous autorisez (A) le Syndicat des Vins IGP à envoyer des instructions à votre banque pour débiter votre compte, et (B) votre banque à débiter votre compte conformément aux instructions du Syndicat des Vins IGP.', 'autorisation correctement configuré');
$t->is(MandatSepaConfiguration::getInstance()->getMentionRemboursement(), 'Vous bénéficiez d\'un droit à remboursement par votre banque selon les conditions décrites dans la convention que vous avez passée avec elle. Toute demande de remboursement doit être présentée dans les 8 semaines suivant la date de débit de votre compte ou sans tarder et au plus tard dans les 13 mois en cas de prélèvement non autorisé.', 'remboursement correctement configuré');
$t->is(MandatSepaConfiguration::getInstance()->getMentionDroits(), 'Vos droits concernant le présent mandat sont expliqués dans un document que vous pouvez obtenir auprès de votre banque.', 'droits correctement configuré');

$mandatSepa = MandatSepaClient::getInstance()->createDoc($societe);
$mandatSepa->constructId();
$mandatSepa->save();
$mandaid = $mandatSepa->_id;
$mandatSepa = MandatSepaClient::getInstance()->find($mandaid);

$t->is($mandatSepa->date, date('Y-m-d'), 'date de creation = date du jour');
$t->is($mandatSepa->is_signe, 0, 'mandat non signe');
$t->is($mandatSepa->debiteur->frequence_prelevement, MandatSepaClient::FREQUENCE_PRELEVEMENT_RECURRENT, 'fréquence = récurrent');

$t->is($mandatSepa->mention_autorisation, MandatSepaConfiguration::getInstance()->getMentionAutorisation(), 'autorisation conforme à la configuration');
$t->is($mandatSepa->mention_remboursement, MandatSepaConfiguration::getInstance()->getMentionRemboursement(), 'remboursement conforme à la configuration');
$t->is($mandatSepa->mention_droits, MandatSepaConfiguration::getInstance()->getMentionDroits(), 'droits conforme à la configuration');

$t->is($mandatSepa->creancier->identifiant_ics, MandatSepaConfiguration::getInstance()->getMandatSepaIdentifiant(), 'ics conforme à la configuration');
$t->is($mandatSepa->creancier->nom, MandatSepaConfiguration::getInstance()->getMandatSepaNom(), 'nom conforme à la configuration');
$t->is($mandatSepa->creancier->adresse, MandatSepaConfiguration::getInstance()->getMandatSepaAdresse(), 'adresse conforme à la configuration');
$t->is($mandatSepa->creancier->code_postal, MandatSepaConfiguration::getInstance()->getMandatSepaCodePostal(), 'cp conforme à la configuration');
$t->is($mandatSepa->creancier->commune, MandatSepaConfiguration::getInstance()->getMandatSepaCommune(), 'commune conforme à la configuration');

$t->is($mandatSepa->debiteur->identifiant_rum, $societe->identifiant, 'rum conforme à la societe');
$t->is($mandatSepa->debiteur->nom, $societe->raison_sociale, 'nom conforme à la societe');
$t->is($mandatSepa->debiteur->adresse, $societe->siege->adresse, 'adresse conforme à la societe');
$t->is($mandatSepa->debiteur->code_postal, $societe->siege->code_postal, 'cp conforme à la societe');
$t->is($mandatSepa->debiteur->commune, $societe->siege->commune, 'commune conforme à la societe');

$id = 'MANDATSEPA-'.$societe->getIdentifiant().'-'.date('Ymd');
$t->is($mandatSepa->_id, $id, 'identifiant de mandat SEPA conforme');

//Switch des prélèvement actif et mandat signé voir si ils sont cochés.

$t->is($mandatSepa->is_signe,0,'le mandat n\'est pas signé');
$mandatSepa->switchIsSigne();
$t->is($mandatSepa->is_signe,1,'le mandat est signé');
$t->is($mandatSepa->is_actif,1,'le prélevement est actif');

$mandatSepa->switchIsActif();
$t->is($mandatSepa->is_actif,0,'le prélevement n\'est plus actif');
$mandatSepa->switchIsActif();

$mandatSepa->save();

$t->comment("Création de la drev à facturer");

$config = ConfigurationClient::getCurrent();
$produit_hash = null;
foreach($config->getProduits() as $hash => $produit) {
    $produit_hash = $produit->getHash();
    break;
}

$periode = (date('Y')-1)."";

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $periode);
$drev->save();

$lot = $drev->addLot();
$lot->numero_logement_operateur = 'CUVE';
$lot->produit_hash = $produit_hash;
$lot->volume = 100;
$drev->save();

$drev->validate();
$drev->validateOdg();
$drev->save();

//Création de la facture
$societe->add('region',"IGP13");
$societe->save();
$mouvementsBySoc = array($societe->identifiant => FactureClient::getInstance()->getFacturationForSociete($societe));
$mouvementsBySoc = FactureClient::getInstance()->filterWithParameters($mouvementsBySoc,array("date_mouvement" => date('Y-m-d')));
$facture = FactureClient::getInstance()->createDocFromView($mouvementsBySoc[$societe->getIdentifiant()],$societe->getMasterCompte());

$facture->save();
$t->is(count($facture->paiements), 1, "La facture générée a bien un paiement");

$t->is($facture->paiements[0]->type_reglement, FactureClient::FACTURE_PAIEMENT_PRELEVEMENT_AUTO, "Le paiement est bien un prélèvement automatique");
$t->is($facture->paiements[0]->montant, 89.9, "La montant du prélèvement automatique est bien de 89.9 TTC");
$t->is($facture->paiements[0]->execute, false, "Le prélèvement n'est pas encore executé");
$t->is($facture->paiements[0]->date, date('Y-m-d', strtotime('+15 days')), "Le prélèvement est bien inscrit pour dans 15 jours");

//Appel à la vue

$facturesEnAttenteSepa = FactureEtablissementView::getInstance()->getPaiementNonExecuteSepa();

$t->is(count($facturesEnAttenteSepa), 1, "Il y a bien un prelevement en attente de SEPA");
$t->is($facturesEnAttenteSepa[0]->id, $facture->_id, "La facture est bien en attente de versement sepa");

$sepa = ExportXMlSEPA::getExportXMLSepaForCurrentPrelevements(true);

$xml = $sepa->getXml();
$sepa->saveExportedSepa();

$date = date('Y-m-d\TH:i:00\Z');
$date_signature = date('Y-m-d');
$t->is($xml, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<Document xmlns=\"urn:iso:std:iso:20022:tech:xsd:pain.008.001.02\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"urn:iso:std:iso:20022:tech:xsd:pain.008.001.02 pain.008.001.02.xsd\"><CstmrDrctDbtInitn><GrpHdr><MsgId>".date('Y-m-d-h-i-00')."</MsgId><CreDtTm>".$date."</CreDtTm><NbOfTxs>1</NbOfTxs><CtrlSum>89.9</CtrlSum><InitgPty><Nm>Syndicat</Nm><Id><OrgId><Othr><Id>FR123456789123974747 (BICXXXXXX)</Id></Othr></OrgId></Id></InitgPty></GrpHdr></CstmrDrctDbtInitn><PmtInf><PmtInfId>PAIEMENT-".$facture->paiements[0]->date."</PmtInfId><PmtMtd>DD</PmtMtd><NbOfTxs>1</NbOfTxs><CtrlSum>89.9</CtrlSum><PmtTpInf><SvcLvl><Cd>SEPA</Cd></SvcLvl><LclInstrum><Cd>CORE</Cd></LclInstrum><SeqTp>RCUR</SeqTp></PmtTpInf><ReqdColltnDt>".date('Y-m-d',strtotime($date.'+15 days'))."</ReqdColltnDt><Cdtr><Nm>Syndicat</Nm></Cdtr><CdtrAcct><Id><IBAN>FR123456789123974747 (BICXXXXXX)</IBAN></Id></CdtrAcct><CdtrAgt><FinInstnId><BIC/></FinInstnId></CdtrAgt><ChrgBr>SLEV</ChrgBr><CdtrSchmeId><Id><PrvtId><Othr><Id/><SchmeNm><Prtry>SEPA</Prtry></SchmeNm></Othr></PrvtId></Id></CdtrSchmeId><DrctDbtTxInf><PmtId><EndToEndId>Syndicat Facture</EndToEndId></PmtId><InstdAmt Ccy=\"EUR\">89.9</InstdAmt><DrctDbtTx><MndtRltdInf><MndtId>BDR000005</MndtId><DtOfSgntr>".$date_signature."</DtOfSgntr></MndtRltdInf></DrctDbtTx><DbtrAgt><FinInstnId><BIC/></FinInstnId></DbtrAgt><Dbtr><Nm>SARL ACTUALYS JEAN</Nm></Dbtr><DbtrAcct><Id><IBAN/></Id></DbtrAcct><RmtInf><Ustrd>Facture</Ustrd></RmtInf></DrctDbtTxInf></PmtInf></Document>
","Le XML généré correspond à notre montant");

$facturesEnAttenteSepa = FactureEtablissementView::getInstance()->getPaiementNonExecuteSepa();
$t->is(count($facturesEnAttenteSepa), 0, "Il n'y a plus de prelevement en attente de SEPA");
