<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

sfContext::createInstance($configuration);

$t = new lime_test(62);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des habilitations précédentes
foreach(HabilitationClient::getInstance()->getHistory($viti->identifiant) as $k => $v) {
  $habilitation = HabilitationClient::getInstance()->find($k);
  $habilitation->delete(false);
}

$config = ConfigurationClient::getCurrent();

$produitConfig = null;
foreach($config->getProduitsCahierDesCharges() as $p) {
    $produitConfig = $p;
    break;
}

$demandeStatut = "HABILITATION";

$t->comment("Création d'une demande");
$date = (new DateTime("-6 month"))->format('Y-m-d');
$statut = "DEPOT";
$commentaire = "Envoyé par courrier";
$auteur = "Syndicat";
$activites = array(HabilitationClient::ACTIVITE_VINIFICATEUR, HabilitationClient::ACTIVITE_ELABORATEUR);

$demande = HabilitationClient::getInstance()->createDemandeAndSave($viti->identifiant, $demandeStatut, array('produit' => $produitConfig->getHash(), 'activites' => $activites), $statut, $date, $commentaire,  $auteur);
$habilitation = $demande->getDocument();

$demande2 = HabilitationClient::getInstance()->createDemandeAndSave($viti->identifiant, $demandeStatut, array('produit' => $produitConfig->getHash(), 'activites' => $activites), $statut, $date, $commentaire,  $auteur);

$key2 = $viti->identifiant."-".str_replace("-", "", $date)."02";
$t->is($demande2->getKey(), $key2, "La clé de la seconde demande est : ".$key2);

$idDocHabilitation = 'HABILITATION-'.$viti->identifiant.'-'.str_replace('-', '', $date);
$keyDemande1 = $viti->identifiant."-".str_replace("-", "", $date)."01";
$t->is($habilitation->_id, $idDocHabilitation, "L'id du doc d'habilitation est ".$idDocHabilitation);
$t->ok($demande instanceof HabilitationDemande, "La demande est une instance de HabilitationDemande");
$t->is($demande->getKey(), $keyDemande1, "La clé de la demande est ".$keyDemande1);
$t->is($demande->donnees->produit, $produitConfig->getHash(), "La hash produit est ".$produitConfig->getHash());
$t->is($demande->libelle, $produitConfig->getLibelleComplet().", Activités: Vinificateur, Élaborateur", "Le libellé produit est ".$produitConfig->getLibelleComplet());
$t->is($demande->donnees->activites->toArray(true, false), $activites, "Les activites sont bien stockées");
$t->is($demande->date, $date, "La date du statut est ".$date);
$t->is($demande->demande, $demandeStatut, "La demande est ".$demandeStatut);
$t->is($demande->statut, $statut, "La statut de la demande est ".$statut);

$t->is(count($habilitation->historique), 1, "L'historique de cette habilitation a 1 élément");
$t->is($habilitation->historique->get(0)->iddoc, $habilitation->_id.":".$demande->getHash(), "L'id du doc contient la hash");
$t->is($habilitation->historique->get(0)->description, "La demande d'habilitation \"".$produitConfig->getLibelleComplet().", Activités: Vinificateur, Élaborateur\" a été créée au statut \"Dépôt\"", "La description de l'action est ok");
$t->is($habilitation->historique->get(0)->statut, $demande->statut, "Le statut de la demande est dans l'historique");
$t->is($habilitation->historique->get(0)->commentaire, $commentaire, "Le commentaire est ".$commentaire);
$t->is($habilitation->historique->get(0)->date, $date, "La date est ".$date);

$t->comment("Changement de statut de la 1ère demande");

$date = (new DateTime("now - 3 month"))->format('Y-m-d');
$statut = "COMPLET";
$commentaire = "Après 3 relance";
$auteur = "Syndicat";

$demande = HabilitationClient::getInstance()->updateDemandeAndSave($viti->identifiant, $keyDemande1, $date, $statut, $commentaire, $auteur);
$habilitation = $demande->getDocument();

$idDocHabilitation = 'HABILITATION-'.$viti->identifiant.'-'.str_replace('-', '', $date);
$t->is($habilitation->_id, $idDocHabilitation, "L'id du doc d'habilitation est ".$idDocHabilitation);

$t->is($demande->getKey(), $keyDemande1, "La clé de la demande est ".$keyDemande1);
$t->is($demande->date, $date, "La date du statut est ".$date);
$t->is($demande->statut, $statut, "La statut de la demande est ".$statut);

$t->is(count($habilitation->historique), 1, "L'historique de cette habilitation a 1 élément");
$t->is($habilitation->historique->get(0)->description, "La demande d'habilitation \"".$produitConfig->getLibelleComplet().", Activités: Vinificateur, Élaborateur\" est passée au statut \"Complet\"", "La description de l'action est ok");
$t->is($habilitation->historique->get(0)->commentaire, $commentaire, "Le commentaire est ".$commentaire);
$t->is($habilitation->historique->get(0)->date, $date, "La date est ".$date);

$t->comment("Validation de la 1ère demande");

$date = (new DateTime("now"))->format('Y-m-d');
$statut = "VALIDE";
$commentaire = "Cool";
$auteur = "Syndicat";

$demande = HabilitationClient::getInstance()->updateDemandeAndSave($viti->identifiant, $keyDemande1, $date, $statut, $commentaire, $auteur);
$habilitation = $demande->getDocument();

$idDocHabilitation = 'HABILITATION-'.$viti->identifiant.'-'.str_replace('-', '', $date);
$t->is($habilitation->_id, $idDocHabilitation, "L'id du doc d'habilitation est ".$idDocHabilitation);

$habilitationLast = HabilitationClient::getInstance()->getLastHabilitation($viti->identifiant);

$t->ok($habilitationLast->exist($demande->donnees->produit), "Le produit a été créé dans l'habilitation");
$habilitationProduit = $habilitationLast->get($demande->donnees->produit);
$t->is($habilitationProduit->activites->get($activites[0])->statut, "HABILITE", "La première activité est habilité");
$t->is($habilitationProduit->activites->get($activites[0])->date, $date, "La première activité est à pour date ".$date);
$t->is($habilitationProduit->activites->get($activites[0])->commentaire, $commentaire, "La première activité est à pour commentaire ".$commentaire);

$t->comment("Ajout d'un statut antérieur pour la 1ère demande");

$date = (new DateTime("now -2 month"))->format('Y-m-d');
$statut = "TRANSMIS_OIVR";
$commentaire = "Transmis à l'OIVR";
$auteur = "Syndicat";

$demande = HabilitationClient::getInstance()->updateDemandeAndSave($viti->identifiant, $keyDemande1, $date, $statut, $commentaire, $auteur);
$habilitation = $demande->getDocument();

$idDocHabilitation = 'HABILITATION-'.$viti->identifiant.'-'.str_replace('-', '', $date);
$t->is($habilitation->_id, $idDocHabilitation, "L'id du doc d'habilitation est ".$idDocHabilitation);

$habilitation = $demande->getDocument();
$habilitationLast = HabilitationClient::getInstance()->getLastHabilitation($viti->identifiant);
$t->is($habilitationLast->demandes->get($demande->getKey())->statut, "VALIDE", "Le statut final est toujour VALIDE");

$t->comment("Création d'une 2ème demande");

$date = (new DateTime("-8 month"))->format('Y-m-d');
$statut = "DEPOT";
$commentaire = "";
$auteur = "Syndicat";
$activites = array(HabilitationClient::ACTIVITE_PRODUCTEUR);

$demande = HabilitationClient::getInstance()->createDemandeAndSave($viti->identifiant, $demandeStatut, array("produit" => $produitConfig->getHash(), "activites" => $activites), $statut, $date, $commentaire, $auteur);

$habilitation = $demande->getDocument();
$keyDemande2 = $demande->getKey();
$idDocHabilitation = 'HABILITATION-'.$viti->identifiant.'-'.str_replace('-', '', $date);
$t->is($habilitation->_id, $idDocHabilitation, "L'id du doc d'habilitation est ".$idDocHabilitation);

$habilitationLast = HabilitationClient::getInstance()->getLastHabilitation($viti->identifiant);
$t->is($habilitationLast->demandes->get($demande->getKey())->toArray(true, false), $demande->toArray(true, false), "La demande a bien été repliquée sur l'habilitation la plus récente");

$t->comment("Changement de statut de la 2ème demande");

$date = (new DateTime("now - 4 month"))->format('Y-m-d');
$statut = "COMPLET";
$commentaire = "Directement";
$auteur = "Syndicat";

$demande = HabilitationClient::getInstance()->updateDemandeAndSave($viti->identifiant, $keyDemande2, $date, $statut, $commentaire, $auteur);
$habilitation = $demande->getDocument();

$idDocHabilitation = 'HABILITATION-'.$viti->identifiant.'-'.str_replace('-', '', $date);
$t->is($habilitation->_id, $idDocHabilitation, "L'id du doc d'habilitation est ".$idDocHabilitation);

$habilitationLast = HabilitationClient::getInstance()->getLastHabilitation($viti->identifiant);
$t->is($habilitationLast->demandes->get($demande->getKey())->toArray(true, false), $demande->toArray(true, false), "La changement de la demande a bien été repliquée sur l'habilitation la plus récente");

$t->comment("Création d'une demande de changement de cvi");
$date = (new DateTime("-12 month"))->format('Y-m-d');
$statut = "DEPOT";
$commentaire = "Demande de changement de CVI";
$auteur = "Syndicat";
$cvi = "7523700100";
$raisonSociale = "MR LE PRESIDENT";

$demande = HabilitationClient::getInstance()->createDemandeAndSave($viti->identifiant, "DECLARANT", array('cvi' => $cvi, 'raison_sociale' => $raisonSociale), $statut, $date, $commentaire,  $auteur);
$habilitation = $demande->getDocument();

$idDocHabilitation = 'HABILITATION-'.$viti->identifiant.'-'.str_replace('-', '', $date);
$keyDemande1 = $viti->identifiant."-".str_replace("-", "", $date)."01";
$t->is($habilitation->_id, $idDocHabilitation, "L'id du doc d'habilitation est ".$idDocHabilitation);
$t->is($demande->libelle, "Cvi: ".$cvi.", Raison sociale: ".$raisonSociale, "Le libellé de la demande est ok");
$t->is($demande->donnees->cvi, $cvi, "Le CVI est stocké");
$t->is($demande->donnees->raison_sociale, $raisonSociale, "La raison sociale est stocké");

$t->comment("Validation de la demande de changement de cvi");

$date = (new DateTime("now"))->format('Y-m-d');
$statut = "VALIDE";
$commentaire = "Cool";
$auteur = "Syndicat";

$demande = HabilitationClient::getInstance()->updateDemandeAndSave($viti->identifiant, $keyDemande1, $date, $statut, $commentaire, $auteur);
$habilitation = $demande->getDocument();

$idDocHabilitation = 'HABILITATION-'.$viti->identifiant.'-'.str_replace('-', '', $date);
$t->is($habilitation->_id, $idDocHabilitation, "L'id du doc d'habilitation est ".$idDocHabilitation);

$habilitationLast = HabilitationClient::getInstance()->getLastHabilitation($viti->identifiant);

$t->is($habilitationLast->declarant->cvi, $cvi, "Le CVI a été modifiée dans la dernière habilitation");
$t->is($habilitationLast->declarant->raison_sociale, $raisonSociale, "La raison sociale a été modifiée dans la dernière habilitation");

$t->comment("Création d'une demande d'habilitation produit par formulaire");

$habilitation = HabilitationClient::getInstance()->getLastHabilitation($viti->identifiant);

$form = new HabilitationDemandeCreationProduitForm($habilitation);

$defaults = $form->getDefaults();

$t->is($defaults, array('_revision' => $habilitation->_rev, 'donnees' => array()), "Aucune valeur par défaut");

$values = array(
    '_revision' => $habilitation->_rev,
    'donnees' => array(
        'produit' => $produitConfig->getHash(),
        'activites' => array(HabilitationClient::ACTIVITE_VINIFICATEUR, HabilitationClient::ACTIVITE_ELABORATEUR),
    ),
    'demande' => 'RETRAIT',
    'date' => (new DateTime("now - 1 week"))->format('d/m/Y'),
    'statut' => 'DEPOT',
    'commentaire' => "Est venu directement nous voir",
);

$form->bind($values);

$t->ok($form->isValid(), "Le formulaire est valide");

$demande = $form->save();

$habilitation = HabilitationClient::getInstance()->find($demande->getDocument()->_id);
$demande = $habilitation->demandes->get($demande->getKey());
$t->ok($habilitation->demandes->exist($demande->getKey()), "La demande a été créée");
$t->is($demande->donnees->produit, $values['donnees']['produit'], "La hash produit a été enregistrée");
$t->is($demande->donnees->activites->toArray(true, false), $values['donnees']['activites'], "Les activités ont été enregistrées");
$t->is($demande->demande, $values['demande'], "La demande a été enregistrée");
$t->is($demande->date, preg_replace("|([0-9]+)/([0-9]+)/([0-9]+)|", '\3-\2-\1', $values['date']), "La date a été enregistrée");
$t->is($demande->statut, $values['statut'], "Le statut a été enregistrée");
$t->is($habilitation->historique->get(0)->commentaire, $values['commentaire'], "Le commentaire a été enregistrée");

$t->comment("Modification de la demande par formulaire");

$habilitation = HabilitationClient::getInstance()->getLastHabilitation($viti->identifiant);
$demande = $habilitation->demandes->get($demande->getKey());

$form = new HabilitationDemandeEditionForm($demande);

$defaults = $form->getDefaults();

$t->is($defaults, array('_revision' => $habilitation->_rev), "Les valeurs par défaut du formulaire sont diponibles");

$values = array(
    '_revision' => $habilitation->_rev,
    'date' => (new DateTime("now - 1 day"))->format('d/m/Y'),
    'statut' => 'COMPLET',
    'commentaire' => "Le document n'était pas signé",
);

$form->bind($values);

$t->ok($form->isValid(), "Le formulaire est valide");

$demande = $form->save();

$habilitation = HabilitationClient::getInstance()->find($demande->getDocument()->_id);
$demande = $habilitation->demandes->get($demande->getKey());

$t->is($demande->date, preg_replace("|([0-9]+)/([0-9]+)/([0-9]+)|", '\3-\2-\1', $values['date']), "La date a été enregistrée");
$t->is($demande->statut, $values['statut'], "Le statut a été enregistrée");
$t->is($habilitation->historique->get(0)->commentaire, $values['commentaire'], "Le commentaire a été enregistrée");


$t->comment("Création d'une demande de modification d'identification par formulaire");

$habilitation = HabilitationClient::getInstance()->getLastHabilitation($viti->identifiant);

$form = new HabilitationDemandeCreationIdentificationForm($habilitation, array());

$defaults = $form->getDefaults();

$t->is($defaults, array('_revision' => $habilitation->_rev, 'demande' => HabilitationClient::DEMANDE_DECLARANT,'donnees' => array('raison_sociale' => $habilitation->declarant->raison_sociale, 'cvi' => $habilitation->declarant->cvi, 'siret' => $habilitation->declarant->siret, 'adresse' => $habilitation->declarant->adresse, 'code_postal' => $habilitation->declarant->code_postal, 'commune' => $habilitation->declarant->commune)), "Les valeurs par défaut du formulaire sont disponibles");

$values = array_merge($defaults, array(
    'date' => (new DateTime("now - 2 day"))->format('d/m/Y'),
    'donnees' => array_merge($defaults['donnees'],
    array(
        'raison_sociale' => 'MANU',
        'siret' => '1234567891011',
        'adresse' => null,
    )),
    'statut' => 'DEPOT',
    'commentaire' => "Changement de gérant",
));

$form->bind($values);

$t->ok($form->isValid(), "Le formulaire est valide");

$demande = $form->save();

$habilitation = HabilitationClient::getInstance()->find($demande->getDocument()->_id);
$demande = $habilitation->demandes->get($demande->getKey());

$t->is($demande->donnees->raison_sociale, $values['donnees']['raison_sociale'], "La raison sociale est dans les données de la demande");
$t->is($demande->donnees->siret, $values['donnees']['siret'], "Le siret est dans les données de la demande");
$t->ok(!$demande->donnees->exist('cvi'), "Le CVI n'est pas dans les données de la demande");
$t->ok(!$demande->donnees->exist('adresse'), "L'adresse n'est pas dans les données de la demande");
