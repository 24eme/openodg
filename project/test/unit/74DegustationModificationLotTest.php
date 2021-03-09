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
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

$degustation = acCouchdbManager::getClient()->find($docid);
if ($degustation == null) {
    exit('Doc null');
}

$first_lot_degust = $degustation->lots->get(0);
$volume = $first_lot_degust->volume;
$first_document_origine = $first_lot_degust->id_document;
$drev = DRevClient::getInstance()->find($first_document_origine);

if($drevModificatrice = DRevClient::getInstance()->find($first_document_origine."-M01")){
  $drevModificatrice->delete(false);
}

$first_lot_drev = $drev->get($first_lot_degust->origine_mouvement);

$t->comment("La condition préalable est que le lot soit prélevable");
$t->is($first_lot_drev->statut,Lot::STATUT_PRELEVABLE, "Le lot de la DRev est bien prélevable");

$newVolume = $volume+100;

$form = new DegustationLotForm($first_lot_degust);
$valuesRev = array(
  '_revision' => $degustation->_rev,
    'volume' => $newVolume,
);
$form->bind($valuesRev);

$t->ok($form->isValid(), "Le formulaire de changement de volume est valide");

$form->save();

$degustation = acCouchdbManager::getClient()->find($docid);
$lot = $degustation->lots->get(0);
$new_document_origine = $lot->id_document;

$t->is($lot->volume, $newVolume, 'Le mouvement dans la degustation a le nouveau volume');

$t->isnt($first_document_origine,$new_document_origine, 'Le document d\'origine a changé il s\'agit maintenant d\'une modificatrice de Drev');

$first_document_origine = DRevClient::getInstance()->find($first_document_origine);

$lot_origine_drev = $first_document_origine->get($first_lot_degust->origine_mouvement);

$t->is($lot_origine_drev->volume, $volume, 'Le mouvement dans la Drev 0 n\'a pas changé');
$t->is($lot_origine_drev->statut, Lot::STATUT_NONPRELEVABLE, 'Le statut dans la Drev 0 est devenu "NON PRELEVABLE"');

$drevModificatrice = DRevClient::getInstance()->find($new_document_origine);
$lot_drev_modif = $drevModificatrice->get($lot->origine_mouvement);

$t->is($lot_drev_modif->volume, $newVolume, 'Le mouvement dans la Drev Modif a le nouveau volume');
$t->is($lot_drev_modif->statut, Lot::STATUT_PRELEVABLE, 'Le statut dans la Drev Modif est "PRELEVABLE"');

$t->is($lot_drev_modif->volume, $newVolume, 'Le mouvement dans la Drev Modif a le nouveau volume');
$t->is($lot_drev_modif->statut, Lot::STATUT_PRELEVABLE, 'Le statut dans la Drev Modif est "PRELEVABLE"');

$mvtOrigine = $drevModificatrice->get($lot->origine_mouvement);

$t->isnt($mvtOrigine, null, 'On retrouve bien le mouvement d\'origine dans la Drev Modif');

// $t->comment('On créé une modificatrice, supprime le lot');
// $modificatrice = $drev->generateModificative();
// $t->is($modificatrice->isModificative(), true, 'La drev créée est une modificatrice');
// $t->is(count($modificatrice->lots), count($drev->lots), 'Il y a le même nombre de lot que l\'originale');
// $modificatrice->lots->remove(0);
// $t->is(count($modificatrice->lots), count($drev->lots) - 1, 'Il y a un lot en moins');
//
// $t->comment('On modifie un lot et on l\'ajoute à la modificatrice');
// $t->comment('Volume original : '.$volume.'hl');
// $lot->volume += 1.12;
// $lot_modificatrice = $modificatrice->addLotFromDegustation($lot);
// $t->is($lot_modificatrice->volume, $volume + 1.12, 'Le volume est mis à jour dans la modificatrice');
//
// $t->comment('On génère les mouvements de lot de la modificatrice');
// $modificatrice->generateMouvementsLots();
// $mvmt_lots_modificatrice = $modificatrice->get("/mouvements_lots/".$viti->identifiant);
// $mvmt_lot_modificatrice = null;
// foreach ($mvmt_lots_modificatrice as $keyMvt => $mvt) {
//   $mvmt_lot_modificatrice = $mvt;
// }
//
// $t->is($mvmt_lot_modificatrice->volume, $volume + 1.12, 'Le volume est à jour dans les mvmts de la modificatrice');
// $t->is($mvmt_lot_modificatrice->origine_hash, '/lots/'.(count($modificatrice->lots) - 1), "L'origine du mvmt est le bon");
// $t->is($mvmt_lot_modificatrice->statut, Lot::STATUT_PRELEVABLE, "Le mouvement est prélevable");
//
// $t->comment('On rend prélevable à 0 le lot dans la DREV originale');
// $modificatrice->save();
//
// $drev = DRevClient::getInstance()->find($lot->id_document);
// $mvmt = $drev->get($lot->origine_mouvement);
//
// $t->is($mvmt->statut,Lot::STATUT_NONPRELEVABLE, 'Le mouvement originel n\'est plus prélevable mais "NONPRELEVABLE"');
//
// $t->comment('On met à jour la dégustation');
// $degustation->updateLot(0, $lot);
//
// $t->comment('Changement dans la dégustation');
// $lot = $degustation->lots->get(0);
// $t->is($lot->volume, $volume + 1.12, 'Le volume est changé');
