<?php

include dirname(__FILE__).'/../../bootstrap/functional.php';

if (getenv("NODELETE")) {
    $b = new sfTestFunctional(new sfBrowser());
    exit(0);
}

foreach (CompteTagsView::getInstance()->listByTags('test', 'test_functionnal') as $k => $v) {
    if (preg_match('/SOCIETE-([^ ]*)/', implode(' ', array_values($v->value)), $m)) {
        $soc = SocieteClient::getInstance()->findByIdentifiantSociete($m[1]);
        foreach($soc->getEtablissementsObj() as $k => $etabl) {
            if($etabl->etablissement){
                foreach(DRevClient::getInstance()->getHistory($etablissement->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
                    $drev = DRevClient::getInstance()->find($k);
                    $drev->delete(false);
                }
            }
        }
        $soc->delete();
    }
}

$b = new sfTestFunctional(new sfBrowser());
