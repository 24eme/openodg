<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(43);

$viti = EtablissementClient::getInstance()->find('ETABLISSEMENT-7523700100');
$t->ok($viti, "L'etablissement 7523700100 existe");
$campagne = (date("Y") - 1)."";

foreach(TravauxMarcClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $travauxMarc = TravauxMarcClient::getInstance()->find($k);
    $travauxMarc->delete(false);
}

$t->comment("Création d'une déclaration d'ouverture de travaux de disitilliation");

$dateDebutDistillation = date("Y")."-03-01";
$dateFinDistillation = date("Y")."-03-31";

$travauxMarc = TravauxMarcClient::getInstance()->createDoc($viti->identifiant, $campagne, true);
$travauxMarc->save();

$t->is($travauxMarc->_id, "TRAVAUXMARC-".$viti->identifiant."-".$campagne, "L'id du doc est "."TRAVAUXMARC-".$viti->identifiant."-".$campagne);
$t->ok($travauxMarc->_rev, "La révision existe");
$t->ok($travauxMarc->isPapier(), "La déclaration est papier");
$t->ok($travauxMarc->adresse_distillation->adresse == $viti->adresse && $travauxMarc->adresse_distillation->code_postal == $viti->code_postal && $travauxMarc->adresse_distillation->commune == $viti->commune,  "L'adresse de distillation est celle de l'établissement");

$t->comment("Étape Exploitation");

if($travauxMarc->storeEtape(TravauxMarcEtapes::ETAPE_EXPLOITATION)) {
    $travauxMarc->save();
}
$t->is($travauxMarc->etape, TravauxMarcEtapes::ETAPE_EXPLOITATION, "L'étape est " . TravauxMarcEtapes::ETAPE_EXPLOITATION);

$travauxMarc->storeDeclarant();
$travauxMarc->save();

$t->is($travauxMarc->declarant->nom, $viti->nom, "Le nom de l'établissement a été stocké dans le doc");
$t->is($travauxMarc->declarant->adresse, $viti->adresse, "L'adresse de l'établissement a été stocké dans le doc");
$t->is($travauxMarc->declarant->code_postal, $viti->code_postal, "Le code postal de l'établissement a été stocké dans le doc");
$t->is($travauxMarc->declarant->commune, $viti->commune, "La commune de l'établissement a été stocké dans le doc");
$t->is($travauxMarc->declarant->email, $viti->email, "L'email de l'établissement a été stocké dans le doc");

$t->comment("Étape Fournisseur de Marcs");

if($travauxMarc->storeEtape(TravauxMarcEtapes::ETAPE_FOURNISSEURS)) {
    $travauxMarc->save();
}
$t->is($travauxMarc->etape, TravauxMarcEtapes::ETAPE_FOURNISSEURS, "L'étape est " . TravauxMarcEtapes::ETAPE_FOURNISSEURS);

$travauxMarc->fournisseurs->add();
$travauxMarc->fournisseurs->add();
$travauxMarc->fournisseurs->add();

$formFournisseurs = new TravauxMarcFournisseursForm($travauxMarc->fournisseurs);

$valuesFormFournissseurs = array(
    array(
        'nom' => "ACTUALYS Jean",
        'date_livraison' => "01/11/".date('Y'),
        'quantite' => 12,
    ),
    array(
        'nom' => "ACTUALYS Jean",
        'date_livraison' => "01/12/".date('Y'),
        'quantite' => 24,
    ),
    array(
        'nom' => null,
        'date_livraison' => null,
        'quantite' => null,
    ),
    '_revision' => $travauxMarc->_rev,
);

$formFournisseurs->bind($valuesFormFournissseurs);

$t->ok($formFournisseurs->isValid(), 'Le formulaire est valide');

$formFournisseurs->save();

$travauxMarc = TravauxMarcClient::getInstance()->find($travauxMarc->_id);

$t->is(count($travauxMarc->fournisseurs), 2, "Le nombre de fournisseurs dans le doc est le même que celui du formulaire");
$t->is($travauxMarc->fournisseurs[0]->nom, "ACTUALYS Jean", "Le nom du viti a éte enregistré");
$t->is($travauxMarc->fournisseurs[0]->quantite, 12, "La quantité a été enregistré");
$t->is($travauxMarc->fournisseurs[0]->date_livraison, date('Y')."-11-01", "La date a été enregistré");

$t->is($travauxMarc->fournisseurs[1]->nom, "ACTUALYS Jean", "Le nom du viti a éte enregistré");
$t->is($travauxMarc->fournisseurs[1]->quantite, 24, "La quantité a été enregistré");
$t->is($travauxMarc->fournisseurs[1]->date_livraison, date('Y')."-12-01", "La date a été enregistré");

$t->comment("Étape Distillation");

if($travauxMarc->storeEtape(TravauxMarcEtapes::ETAPE_DISTILLATION)) {
    $travauxMarc->save();
}
$t->is($travauxMarc->etape, TravauxMarcEtapes::ETAPE_DISTILLATION, "L'étape est " . TravauxMarcEtapes::ETAPE_DISTILLATION);

$formDistillation = new TravauxMarcDistillationForm($travauxMarc);

$valuesDistillation = array(
    'date_distillation' => "30/04/".($campagne + 1),
    'distillation_prestataire' => '1',
    'alambic_connu' => '1',
    'adresse_distillation' => array('adresse' => $viti->adresse,
                                    'code_postal' => $viti->code_postal,
                                    'commune' => $viti->commune),
    '_revision' => $travauxMarc->_rev,
);

$formDistillation->bind($valuesDistillation);

$t->ok($formDistillation->isValid(), 'Le formulaire est valide');

$formDistillation->save();

$travauxMarc = TravauxMarcClient::getInstance()->find($travauxMarc->_id);

$t->is($travauxMarc->date_distillation, ($campagne + 1)."-04-30", "La date de distillation a été enregistré");
$t->is($travauxMarc->distillation_prestataire, true , "La coche prestataire a été enregistré");
$t->is($travauxMarc->alambic_connu, true, "La coche alambic a été enregistré");
$t->is($travauxMarc->adresse_distillation->adresse, $valuesDistillation["adresse_distillation"]["adresse"], "L'adresse a été enregistré");
$t->is($travauxMarc->adresse_distillation->code_postal, $valuesDistillation["adresse_distillation"]["code_postal"], "Le code postal a été enregistré");
$t->is($travauxMarc->adresse_distillation->commune, $valuesDistillation["adresse_distillation"]["commune"], "La commune a été enregistré");

$t->comment("Étape Validation");

if($travauxMarc->storeEtape(TravauxMarcEtapes::ETAPE_VALIDATION)) {
    $travauxMarc->save();
}
$t->is($travauxMarc->etape, TravauxMarcEtapes::ETAPE_VALIDATION, "L'étape est " . TravauxMarcEtapes::ETAPE_VALIDATION);

$travauxMarcAControler = clone $travauxMarc;

$travauxMarcAControler->fournisseurs[0]->date_livraison = null;
$travauxMarcAControler->date_distillation = null;
$travauxMarcAControler->adresse_distillation->code_postal = null;

$controle = new TravauxMarcValidation($travauxMarcAControler);

$t->ok(!$controle->isValide(), "La déclaration a des points bloquants, elle n'est pas valide");
$t->is(count($controle->getErreurs()), 3, "La déclaration a 3 erreurs");

$travauxMarcAControler = clone $travauxMarc;
$travauxMarcAControler->date_distillation = ($campagne + 1) . "-05-01";
$travauxMarcAControler->adresse_distillation->adresse = "48 Jacques Dulud";
$travauxMarcAControler->adresse_distillation->code_postal = "92200";
$travauxMarcAControler->adresse_distillation->commune = "Neuilly-sur-Seine";
$controle = new TravauxMarcValidation($travauxMarcAControler);
$t->is(count($controle->getVigilances()), 2, "La déclaration à 2 point de vigilance");

$controle = new TravauxMarcValidation($travauxMarc);
$t->ok($controle->isValide(), "La déclaration est corrigé, elle est valide");
$t->is(count($controle->getVigilances()), 0, "La déclaration n'a pas de point de vigilance");

$formValidation = new TravauxMarcValidationForm($travauxMarc);

$valuesValidation = array(
    'date' => "01/12/".date('Y'),
    '_revision' => $travauxMarc->_rev,
);

$formValidation->bind($valuesValidation);

$t->ok($formValidation->isValid(), 'Le formulaire est valide');

$t->is($travauxMarc->validation, null, "La date validation n'est pas rempli");

$travauxMarc->validate($formValidation->getValue("date"));
$travauxMarc->save();

$t->is($travauxMarc->validation, date('Y')."-12-01", "La date validation est celle choisi dans le formulaire");

$travauxMarc->validateOdg();
$travauxMarc->save();

$t->is($travauxMarc->validation_odg, date('Y-m-d'), "La date validation par l'odg est la date du jour");

$t->is($travauxMarc->pieces[0]->libelle, "Déclaration d'ouverture des travaux de distillation ".$campagne." (Papier)", "Contrôle sur le libellé du document (pièces)");

$t->comment("Création du document sur la campagne suivante");
$travauxMarcSuivante = TravauxMarcClient::getInstance()->createDoc($viti->identifiant, ($campagne+1)."", true);
$t->is($travauxMarcSuivante->adresse_distillation->adresse, $travauxMarc->adresse_distillation->adresse, "L'adresse a été reprise de la précédente déclaration");
$t->is($travauxMarcSuivante->adresse_distillation->code_postal, $travauxMarc->adresse_distillation->code_postal, "Le code postal a été repris de la précédente déclaration");
$t->is($travauxMarcSuivante->adresse_distillation->commune, $travauxMarc->adresse_distillation->commune, "La commune a été reprise de la précédente déclaration");

$t->comment("Export CSV du document");

$export = new ExportTravauxMarcCSV($travauxMarc, true);

$csv = $export->export();

$t->is(count(explode("\n", $csv)), 3+1, "Les csv a 3 lignes");
