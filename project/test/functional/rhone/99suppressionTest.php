<?php

include dirname(__FILE__).'/../../bootstrap/functional.php';

if (getenv("NODELETE")) {
    $b = new sfTestFunctional(new sfBrowser());
    exit(0);
}

foreach (CompteTagsView::getInstance()->listByTags('test', 'test_functionnal') as $k => $v) {
    if (preg_match('/SOCIETE-([^ ]*)/', implode(' ', array_values($v->value)), $m)) {
        $soc = SocieteClient::getInstance()->findByIdentifiantSociete($m[1]);
        $soc->delete();
    }
}

$b = new sfTestFunctional(new sfBrowser());
