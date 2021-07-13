<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$routing = clone ProjectConfiguration::getAppRouting();
$context->set('routing', $routing);

$t = new lime_test(1);

$coop =  EtablissementClient::getInstance()->find('ETABLISSEMENT-7523700600');
$compte = $coop->getCompte();

foreach(DRevClient::getInstance()->getHistory($coop->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $drev = DRevClient::getInstance()->find($k);
    $drev->delete(false);
}

$campagne = (date('Y')-1)."";

$drev = DRevClient::getInstance()->createDoc($coop->identifiant, $campagne);
$drev->save();

$t->is($drev->isNonRecoltant(), true, "La drev n'est pas en mode recoltant.");
