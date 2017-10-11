<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');
sfContext::createInstance($configuration);

$t = new lime_test(20);

$viti = EtablissementClient::getInstance()->find('ETABLISSEMENT-7523700100');
$vitiCompte = $viti->getCompte();
$campagne = (date('Y')-1)."";

foreach(TravauxMarcClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $travauxMarc = TravauxMarcClient::getInstance()->find($k);
    $travauxMarc->delete(false);
}

$t->comment("Création d'une déclaration d'ouverture de travaux de disitilliation");

$dateDebutDistillation = date("Y")."-03-01";
$dateFinDistillation = date("Y")."-03-31";

$travauxMarc = TravauxMarcClient::getInstance()->createDoc($viti->identifiant, $campagne);
$travauxMarc->save();

$t->is($travauxMarc->_id, "TRAVAUXMARC-".$viti->identifiant."-".$campagne, "L'id du doc est "."TRAVAUXMARC-".$viti->identifiant."-".$campagne);
$t->ok($travauxMarc->_rev, "La révision existe");

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
        'etablissement_id' => "ETABLISSEMENT-7523700100",
        'date_livraison' => "01/11/".date('Y'),
        'quantite' => 12,
    ),
    array(
        'etablissement_id' => "ETABLISSEMENT-7523700100",
        'date_livraison' => "01/12/".date('Y'),
        'quantite' => 24,
    ),
    array(
        'etablissement_id' => null,
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
$t->is($travauxMarc->fournisseurs[0]->quantite, 12, "La quantité a été enregistré");
$t->is($travauxMarc->fournisseurs[0]->date_livraison, date('Y')."-11-01", "La date a été enregistré");
$t->is($travauxMarc->fournisseurs[0]->nom, $viti->nom, "Le nom du viti a éte enregistré");

$t->is($travauxMarc->fournisseurs[1]->quantite, 24, "La quantité a été enregistré");
$t->is($travauxMarc->fournisseurs[1]->date_livraison, date('Y')."-12-01", "La date a été enregistré");
$t->is($travauxMarc->fournisseurs[1]->nom, $viti->nom, "Le nom du viti a éte enregistré");

$t->comment("Étape Distillation");

if($travauxMarc->storeEtape(TravauxMarcEtapes::ETAPE_DISTILLATION)) {
    $travauxMarc->save();
}
$t->is($travauxMarc->etape, TravauxMarcEtapes::ETAPE_DISTILLATION, "L'étape est " . TravauxMarcEtapes::ETAPE_DISTILLATION);

$formDistillation = new TravauxMarcDistillationForm($travauxMarc);

$valuesDistillation = array(
    'date_distillation' => "01/03/".(date('Y')+1),
    'distillation_prestataire' => '1',
    'alambic_connu' => '1',
    'adresse_distillation' => array('adresse' => '48 rue Jacques Dulud',
                                    'code_postal' => '92200',
                                    'commune' => 'Neuilly-sur-Seine'),
    '_revision' => $travauxMarc->_rev,
);

$formDistillation->bind($valuesDistillation);

$t->ok($formDistillation->isValid(), 'Le formulaire est valide');

$formDistillation->save();

$travauxMarc = TravauxMarcClient::getInstance()->find($travauxMarc->_id);

$t->is($travauxMarc->date_distillation, (date('Y')+1)."-03-01", "La date de distillation a été enregistré");
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

$travauxMarc->validate();
$travauxMarc->save();

$t->is($travauxMarc->validation, date('Y-m-d'), "La date validation est la date du jour");

$travauxMarc->validateOdg();
$travauxMarc->save();

$t->is($travauxMarc->validation_odg, date('Y-m-d'), "La date validation par l'odg est la date du jour");

$t->is($travauxMarc->pieces[0]->libelle, "Déclaration d'ouverture des travaux de distillation ".$campagne." (Télédéclaration)", "Contrôle sur le libellé du document (pièces)");
