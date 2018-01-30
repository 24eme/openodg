<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(47);

$viti =  EtablissementClient::getInstance()->find('ETABLISSEMENT-7523700100');
$compte = $viti->getCompte();

foreach(RegistreVCIClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $registre = RegistreVCIClient::getInstance()->find($k);
    $registre->delete(false);
}

$campagne = (date('Y')-1)."";
$societe = $compte;

$t->comment("Création d'un registre VCI");

$registre = RegistreVCIClient::getInstance()->createDoc($viti->identifiant, $campagne);
$registre->save();

$t->is($registre->identifiant, $viti->identifiant, "L'identifiant est celui du viti : ".$viti->identifiant);
$t->is($registre->campagne, $campagne, "La campagne est ".$campagne);

$t->comment("constitution du VCI");

$produits = $registre->getConfigProduits();
foreach($produits as $produit) {
    $produit_hash = $produit->getHash();
    break;
}

$registre->addMouvement($produit, RegistreVCIClient::MOUVEMENT_CONSTITUE, 10, RegistreVCIClient::LIEU_CAVEPARTICULIERE);
$produit_hash = preg_replace('/\/*declaration\/*/', '', $produit_hash);
$t->is($registre->declaration->get($produit_hash)->details->get(RegistreVCIClient::LIEU_CAVEPARTICULIERE)->constitue, 10, "L'ajout d'un mouvement constitué génère un stock contitué");
$t->is($registre->declaration->get($produit_hash)->details->get(RegistreVCIClient::LIEU_CAVEPARTICULIERE)->stock_final, 10, "L'ajout d'un mouvement impacte le stock du detail");
$t->is($registre->declaration->get($produit_hash)->constitue, 10, "L'ajout d'un mouvement constitué génère un stock contitué au niveau produit");
$t->is($registre->declaration->get($produit_hash)->stock_final, 10, "L'ajout d'un mouvement impacte le stock du produit");
$t->is(count($registre->mouvements), 1, 'a un mouvement');
$t->is($registre->mouvements[0]->volume, 10, 'volume enregistré dans le mouvemnt');
$t->is($registre->mouvements[0]->stock_resultant, 10, 'stock enregistré dans le mouvemnt');
$t->is($registre->mouvements[0]->date, $registre->campagne.'-10-15', 'date du mouvement à la date de dépot de la DR');
$t->is($registre->mouvements[0]->produit_hash, $produit_hash, 'hash produit');
$t->is($registre->mouvements[0]->produit_libelle, $produit->getLibelleComplet(), 'libelle produit');
$t->is($registre->mouvements[0]->detail_hash, RegistreVCIClient::LIEU_CAVEPARTICULIERE, 'libelle détail');
$t->is($registre->mouvements[0]->detail_libelle, 'Cave particulière', 'libelle détail');
$t->is($registre->mouvements[0]->mouvement_type, RegistreVCIClient::MOUVEMENT_CONSTITUE, 'mvt type');

$t->comment("VCI rafraichi");
$registre->addMouvement($produit, RegistreVCIClient::MOUVEMENT_RAFRAICHI, 10, RegistreVCIClient::LIEU_CAVEPARTICULIERE);
$t->is($registre->declaration->get($produit_hash)->details->get(RegistreVCIClient::LIEU_CAVEPARTICULIERE)->rafraichi, 10, "L'ajout d'un mouvement RAFRAICHI génère un stock rafraichi");
$t->is($registre->declaration->get($produit_hash)->details->get(RegistreVCIClient::LIEU_CAVEPARTICULIERE)->stock_final, 20, "L'ajout d'un mouvement impacte le stock du detail");
$t->is($registre->declaration->get($produit_hash)->rafraichi, 10, "L'ajout du mouvement génère un stock rafraichi au niveau produit");
$t->is($registre->declaration->get($produit_hash)->stock_final, 20, "L'ajout d'un mouvement impacte le stock du produit");
$t->is(count($registre->mouvements), 2, 'a un mouvement');
$t->is($registre->mouvements[1]->volume, 10, 'volume enregistré dans le mouvemnt');
$t->is($registre->mouvements[1]->stock_resultant, 20, 'stock enregistré dans le mouvemnt');
$t->is($registre->mouvements[1]->date, $registre->campagne.'-10-15', 'date du mouvement à la date de la fin de l\'année suivante');

$t->comment("substitution de VCI");
$registre->addMouvement($produit, RegistreVCIClient::MOUVEMENT_SUBSTITUTION, 10, RegistreVCIClient::LIEU_CAVEPARTICULIERE);
$t->is($registre->declaration->get($produit_hash)->details->get(RegistreVCIClient::LIEU_CAVEPARTICULIERE)->substitution, 10, "L'ajout d'un mouvement substitution génère un stock substitué");
$t->is($registre->declaration->get($produit_hash)->details->get(RegistreVCIClient::LIEU_CAVEPARTICULIERE)->stock_final, 10, "L'ajout d'un mouvement impacte le stock du detail");
$t->is($registre->declaration->get($produit_hash)->substitution, 10, "L'ajout du mouvement génère un stock substitution au niveau produit");
$t->is($registre->declaration->get($produit_hash)->stock_final, 10, "L'ajout d'un mouvement impacte le stock du produit");
$t->is(count($registre->mouvements), 3, 'a un mouvement');
$t->is($registre->mouvements[2]->volume, 10, 'volume enregistré dans le mouvemnt');
$t->is($registre->mouvements[2]->stock_resultant, 10, 'stock enregistré dans le mouvemnt');
$t->is($registre->mouvements[2]->date, ($registre->campagne + 1).'-12-31', 'date du mouvement à la date de dépot de la DR');


$t->comment("VCI detruit");
$registre->addMouvement($produit, RegistreVCIClient::MOUVEMENT_DESTRUCTION, 5, RegistreVCIClient::LIEU_CAVEPARTICULIERE);
$t->is($registre->declaration->get($produit_hash)->details->get(RegistreVCIClient::LIEU_CAVEPARTICULIERE)->destruction, 5, "L'ajout d'un mouvement detruction génère un stock detruction");
$t->is($registre->declaration->get($produit_hash)->details->get(RegistreVCIClient::LIEU_CAVEPARTICULIERE)->stock_final, 5, "L'ajout d'un mouvement impacte le stock du detail");
$t->is($registre->declaration->get($produit_hash)->destruction, 5, "L'ajout du mouvement génère un stock detruction au niveau produit");
$t->is($registre->declaration->get($produit_hash)->stock_final, 5, "L'ajout d'un mouvement impacte le stock du produit");
$t->is(count($registre->mouvements), 4, 'a un mouvement');
$t->is($registre->mouvements[3]->volume, 5, 'volume enregistré dans le mouvemnt');
$t->is($registre->mouvements[3]->stock_resultant, 5, 'stock enregistré dans le mouvemnt');
$t->is($registre->mouvements[3]->date, ($registre->campagne + 1).'-12-31', 'date du mouvement à la date de la fin de l\'année suivante');

$t->comment("VCI complément");
$registre->addMouvement($produit, RegistreVCIClient::MOUVEMENT_COMPLEMENT, 5, RegistreVCIClient::LIEU_CAVEPARTICULIERE);
$t->is($registre->declaration->get($produit_hash)->details->get(RegistreVCIClient::LIEU_CAVEPARTICULIERE)->complement, 5, "L'ajout d'un mouvement complement génère un stock complement");
$t->is($registre->declaration->get($produit_hash)->details->get(RegistreVCIClient::LIEU_CAVEPARTICULIERE)->stock_final, 0, "L'ajout d'un mouvement impacte le stock du detail");
$t->is($registre->declaration->get($produit_hash)->complement, 5, "L'ajout du mouvement génère un stock complement au niveau produit");
$t->is($registre->declaration->get($produit_hash)->stock_final, 0, "L'ajout d'un mouvement impacte le stock du produit");
$t->is(count($registre->mouvements), 5, 'a un mouvement');
$t->is($registre->mouvements[4]->volume, 5, 'volume enregistré dans le mouvemnt');
$t->is($registre->mouvements[4]->stock_resultant, 0, 'stock enregistré dans le mouvemnt');
$t->is($registre->mouvements[4]->date, ($registre->campagne + 1).'-12-31', 'date du mouvement à la date de la fin de l\'année suivante');

$registre->save();
