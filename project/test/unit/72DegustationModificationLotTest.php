<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Pass AOC");
    return;
}

$t = new lime_test();

$campagne = (string) date('Y') - 1;
$degust_date = $campagne.'-09-01 12:45';
$docid = "DEGUSTATION-".str_replace("-", "", preg_replace("/(.+) (.+):(.+)$/","$1$2$3",$degust_date))."-SYNDICAT-VIGNERONS-ARLES";

$degustation = acCouchdbManager::getClient()->find($docid);
if ($degustation == null) {
    exit('Doc null');
}

$lot = $degustation->lots->get(0);
$volume = $lot->volume;
$drev = DRevClient::getInstance()->find($lot->id_document);

$t->comment('On créé une modificatrice, supprime le lot');
$modificatrice = $drev->generateModificative();
$t->is($modificatrice->isModificative(), true, 'La drev créée est une modificatrice');
$t->is(count($modificatrice->lots), count($drev->lots), 'Il y a le même nombre de lot que l\'originale');
$modificatrice->lots->remove(0);
$t->is(count($modificatrice->lots), count($drev->lots) - 1, 'Il y a un lot en moins');

$t->comment('On modifie un lot et on l\'ajoute à la modificatrice');
$t->comment('Volume original : '.$volume.'hl');
$lot->volume += 1.12;
$lot_modificatrice = $modificatrice->addLotFromDegustation($lot);
$t->is($lot_modificatrice->volume, $volume + 1.12, 'Le volume est mis à jour dans la modificatrice');

$t->comment('On génère les mouvements de lot de la modificatrice');
$modificatrice->generateMouvementsLots();
$mvmt_lot_modificatrice = $modificatrice->get('/mouvements_lots/00008801/DECLARATION-CERTIFICATIONS-IGP-GENRES-TRANQ-APPELLATIONS-APL-MENTIONS-DEFAUT-LIEUX-DEFAUT-COULEURS-ROUGE-CEPAGES-DEFAUT-2019-2-3-12');
$t->is($mvmt_lot_modificatrice->volume, $volume + 1.12, 'Le volume est à jour dans les mvmts de la modificatrice');
$t->is($mvmt_lot_modificatrice->origine_hash, '/lots/'.(count($modificatrice->lots) - 1), "L'origine du mvmt est le bon");
$t->is($mvmt_lot_modificatrice->preleve, 0, "Le mouvement n'est pas prélevé");
$t->is($mvmt_lot_modificatrice->prelevable, 1, "Le mouvement est prélevable");

$t->comment('On rend prélevable à 0 le lot dans la DREV originale');
$mvmt = $drev->get($lot->origine_mouvement);
$mvmt->prelevable = 0;

$t->comment('On met à jour la dégustation');
$degustation->updateLot(0, $lot);

$t->comment('Changement dans la dégustation');
$t->is($lot->volume, $volume + 1.12, 'Le volume est changé');
