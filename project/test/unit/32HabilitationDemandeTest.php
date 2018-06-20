<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

sfContext::createInstance($configuration);

$t = new lime_test(28);

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
$date = (new DateTime("- 6 month"))->format('Y-m-d');
$statut = "DEPOT";
$commentaire = "Envoyé par courrier";
$auteur = "Syndicat";
$activites = array(HabilitationClient::ACTIVITE_VINIFICATEUR, HabilitationClient::ACTIVITE_ELABORATEUR);

$demande = HabilitationClient::getInstance()->createDemandeAndSave($viti->identifiant, $produitConfig->getHash(), $activites, $date, $demandeStatut, $statut, $commentaire, $auteur);
$habilitation = $demande->getDocument();

$idDocHabilitation = 'HABILITATION-'.$viti->identifiant.'-'.str_replace('-', '', $date);
$keyDemande1 = $viti->identifiant."-".str_replace("-", "", $date)."01";
$t->is($habilitation->_id, $idDocHabilitation, "L'id du doc d'habilitation est ".$idDocHabilitation);
$t->ok($demande instanceof HabilitationDemande, "La demande est une instance de HabilitationDemande");
$t->is($demande->getKey(), $keyDemande1, "La clé de la demande est ".$keyDemande1);
$t->is($demande->produit_hash, $produitConfig->getHash(), "La hash produit est ".$produitConfig->getHash());
$t->is($demande->produit_libelle, $produitConfig->getLibelleComplet(), "Le libellé produit est ".$produitConfig->getLibelleComplet());
$t->is($demande->activites->toArray(true, false), $activites, "Les activites sont bien stockées");
$t->is($demande->date, $date, "La date du statut est ".$date);
$t->is($demande->demande, $demandeStatut, "La demande est ".$demandeStatut);
$t->is($demande->statut, $statut, "La statut de la demande est ".$statut);

$t->is(count($habilitation->historique), 1, "L'historique de cette habilitation a 1 élément");
$t->is($habilitation->historique->get(0)->description, "La demande d'habilitation pour le ".$produitConfig->getLibelle()." (Vinificateur, Élaborateur) a été créée au statut Dépôt", "La description de l'action est ok");
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
$t->is($habilitation->historique->get(0)->description, "La demande d'habilitation pour le ".$produitConfig->getLibelle()." (Vinificateur, Élaborateur) a été mise à jour du statut Dépôt au statut Complet", "La description de l'action est ok");
$t->is($habilitation->historique->get(0)->commentaire, $commentaire, "Le commentaire est ".$commentaire);
$t->is($habilitation->historique->get(0)->date, $date, "La date est ".$date);

$t->comment("Validation de la 1ère demande ");

$date = (new DateTime("now"))->format('Y-m-d');
$statut = "VALIDE";
$commentaire = "Cool";
$auteur = "Syndicat";

$demande = HabilitationClient::getInstance()->updateDemandeAndSave($viti->identifiant, $keyDemande1, $date, $statut, $commentaire, $auteur);
$habilitation = $demande->getDocument();

$idDocHabilitation = 'HABILITATION-'.$viti->identifiant.'-'.str_replace('-', '', $date);
$t->is($habilitation->_id, $idDocHabilitation, "L'id du doc d'habilitation est ".$idDocHabilitation);

$t->comment("Ajout d'un statut antérieur pour la 1ère demande");

$date = (new DateTime("now -2 month"))->format('Y-m-d');
$statut = "TRANSMIS";
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

$demande = HabilitationClient::getInstance()->createDemandeAndSave($viti->identifiant, $produitConfig->getHash(), $activites, $date, $demandeStatut, $statut, $commentaire, $auteur);

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
