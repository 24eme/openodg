<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test();

$t->is(Organisme::getCurrentRegion(), strtoupper(sfConfig::get('sf_app')), "La région par défaut est l'application");

$region = Organisme::getCurrentRegion();

$organisme = Organisme::getInstance();

$t->is($organisme->getNom(), $infosOrganisme['service_facturation'], "Adresse de l'organisme");
$t->is($organisme->getAdresse(), $infosOrganisme['adresse'], "Adresse de l'organisme");
$t->is($organisme->getCodePostal(), $infosOrganisme['code_postal'], "Code postal de l'organisme");
$t->is($organisme->getCommune(), $infosOrganisme['ville'], "Commune de l'organisme");
$t->is($organisme->getTelephone(), $infosOrganisme['telephone'], "Téléphone de l'organisme");
$t->is($organisme->getEmail(), $infosOrganisme['email'], "Email de l'organisme");
$t->is($organisme->getResponsable(), $infosOrganisme['responsable'], "Responsable de l'organisme");
$t->is($organisme->getIban(), $infosOrganisme['iban'], "Iban de l'organisme");
$t->is($organisme->getNoTvaIntracommunautaire(), $infosOrganisme['tva_intracom'], "tva intracom de l'organisme");
$t->is($organisme->getSiret(), $infosOrganisme['siret'], "siret de l'organisme");
$t->is($organisme->getOi(), $infosOrganisme['oi'], "oi de l'organisme");
$t->is($organisme->getLogoPdfWebPath(), 'images/pdf/logo_'.strtolower($region).'.jpg', "Chemin relatif du logo");
$t->like($organisme->getLogoPdfPath(), '|/[^/]*/web/'.$organisme->getLogoPdfWebPath().'|', "Chemin complet vers le logo");
$t->is($organisme->getImageSignatureWebPath(), 'images/signatures/signature_'.$region.'.jpg', "Chemin relatif de la signature");
$t->like($organisme->getImageSignaturePath(), '|/[^/]*/web/'.$organisme->getImageSignatureWebPath().'|', "Chemin complet vers la signature");

$t->is($organisme->getInfo('adresse'), $organisme->getAdresse(), "Récupération d'une info par la méthod getInfo");
$t->is($organisme->getInfos()['adresse'], $organisme->getAdresse(), "Récupération des infos par la méthode getInfos");
