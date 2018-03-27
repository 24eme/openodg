<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(70);

$viti =  EtablissementClient::getInstance()->find('ETABLISSEMENT-7523700100');
$compte = $viti->getCompte();

foreach(RegistreVCIClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $registre = RegistreVCIClient::getInstance()->find($k);
    $registre->delete(false);
}

foreach(FactureClient::getInstance()->getFacturesByCompte($compte->identifiant, acCouchdbClient::HYDRATE_JSON) as $k => $v) {
    $facture = FactureClient::getInstance()->find($k);
    $facture->delete(false);
}

$campagne = (date('Y')-1)."";
$societe = $compte;
$compteIdentifiant = $societe->identifiant;

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

$registre->addLigne($produit, RegistreVCIClient::MOUVEMENT_CONSTITUE, 10, RegistreVCIClient::LIEU_CAVEPARTICULIERE);
$produit_hash = preg_replace('/\/*declaration\/*/', '', $produit_hash);
$t->is($registre->declaration->get($produit_hash)->details->get(RegistreVCIClient::LIEU_CAVEPARTICULIERE)->constitue, 10, "L'ajout d'un mouvement constitué génère un stock contitué");
$t->is($registre->declaration->get($produit_hash)->details->get(RegistreVCIClient::LIEU_CAVEPARTICULIERE)->stock_final, 10, "L'ajout d'un mouvement impacte le stock du detail");
$t->is($registre->declaration->get($produit_hash)->constitue, 10, "L'ajout d'un mouvement constitué génère un stock contitué au niveau produit");
$t->is($registre->declaration->get($produit_hash)->stock_final, 10, "L'ajout d'un mouvement impacte le stock du produit");
$t->is(count($registre->lignes), 1, 'a un mouvement');
$t->is($registre->lignes[0]->volume, 10, 'volume enregistré dans le mouvemnt');
$t->is($registre->lignes[0]->stock_resultant, 10, 'stock enregistré dans le mouvemnt');
$t->is($registre->lignes[0]->date, $registre->campagne.'-10-15', 'date du mouvement à la date de dépot de la DR');
$t->is($registre->lignes[0]->produit_hash, $produit_hash, 'hash produit');
$t->is($registre->lignes[0]->produit_libelle, $produit->getLibelleComplet(), 'libelle produit');
$t->is($registre->lignes[0]->detail_hash, RegistreVCIClient::LIEU_CAVEPARTICULIERE, 'libelle détail');
$t->is($registre->lignes[0]->detail_libelle, 'Cave particulière', 'libelle détail');
$t->is($registre->lignes[0]->mouvement_type, RegistreVCIClient::MOUVEMENT_CONSTITUE, 'mvt type');

$t->comment("VCI rafraichi");
$registre->addLigne($produit, RegistreVCIClient::MOUVEMENT_RAFRAICHI, 10, RegistreVCIClient::LIEU_CAVEPARTICULIERE);
$t->is($registre->declaration->get($produit_hash)->details->get(RegistreVCIClient::LIEU_CAVEPARTICULIERE)->rafraichi, 10, "L'ajout d'un mouvement RAFRAICHI génère un stock rafraichi");
$t->is($registre->declaration->get($produit_hash)->details->get(RegistreVCIClient::LIEU_CAVEPARTICULIERE)->stock_final, 20, "L'ajout d'un mouvement impacte le stock du detail");
$t->is($registre->declaration->get($produit_hash)->rafraichi, 10, "L'ajout du mouvement génère un stock rafraichi au niveau produit");
$t->is($registre->declaration->get($produit_hash)->stock_final, 20, "L'ajout d'un mouvement impacte le stock du produit");
$t->is(count($registre->lignes), 2, 'a un mouvement');
$t->is($registre->lignes[1]->volume, 10, 'volume enregistré dans le mouvemnt');
$t->is($registre->lignes[1]->stock_resultant, 20, 'stock enregistré dans le mouvemnt');
$t->is($registre->lignes[1]->date, $registre->campagne.'-10-15', 'date du mouvement à la date de la fin de l\'année suivante');

$t->comment("substitution de VCI");
$registre->addLigne($produit, RegistreVCIClient::MOUVEMENT_SUBSTITUTION, 10, RegistreVCIClient::LIEU_CAVEPARTICULIERE);
$t->is($registre->declaration->get($produit_hash)->details->get(RegistreVCIClient::LIEU_CAVEPARTICULIERE)->substitution, 10, "L'ajout d'un mouvement substitution génère un stock substitué");
$t->is($registre->declaration->get($produit_hash)->details->get(RegistreVCIClient::LIEU_CAVEPARTICULIERE)->stock_final, 10, "L'ajout d'un mouvement impacte le stock du detail");
$t->is($registre->declaration->get($produit_hash)->substitution, 10, "L'ajout du mouvement génère un stock substitution au niveau produit");
$t->is($registre->declaration->get($produit_hash)->stock_final, 10, "L'ajout d'un mouvement impacte le stock du produit");
$t->is(count($registre->lignes), 3, 'a un mouvement');
$t->is($registre->lignes[2]->volume, 10, 'volume enregistré dans le mouvemnt');
$t->is($registre->lignes[2]->stock_resultant, 10, 'stock enregistré dans le mouvemnt');
$t->is($registre->lignes[2]->date, ($registre->campagne + 1).'-12-31', 'date du mouvement à la date de dépot de la DR');


$t->comment("VCI detruit");
$registre->addLigne($produit, RegistreVCIClient::MOUVEMENT_DESTRUCTION, 5, RegistreVCIClient::LIEU_CAVEPARTICULIERE);
$t->is($registre->declaration->get($produit_hash)->details->get(RegistreVCIClient::LIEU_CAVEPARTICULIERE)->destruction, 5, "L'ajout d'un mouvement detruction génère un stock detruction");
$t->is($registre->declaration->get($produit_hash)->details->get(RegistreVCIClient::LIEU_CAVEPARTICULIERE)->stock_final, 5, "L'ajout d'un mouvement impacte le stock du detail");
$t->is($registre->declaration->get($produit_hash)->destruction, 5, "L'ajout du mouvement génère un stock detruction au niveau produit");
$t->is($registre->declaration->get($produit_hash)->stock_final, 5, "L'ajout d'un mouvement impacte le stock du produit");
$t->is(count($registre->lignes), 4, 'a un mouvement');
$t->is($registre->lignes[3]->volume, 5, 'volume enregistré dans le mouvemnt');
$t->is($registre->lignes[3]->stock_resultant, 5, 'stock enregistré dans le mouvemnt');
$t->is($registre->lignes[3]->date, ($registre->campagne + 1).'-12-31', 'date du mouvement à la date de la fin de l\'année suivante');

$t->comment("VCI complément");
$registre->addLigne($produit, RegistreVCIClient::MOUVEMENT_COMPLEMENT, 5, RegistreVCIClient::LIEU_CAVEPARTICULIERE);
$t->is($registre->declaration->get($produit_hash)->details->get(RegistreVCIClient::LIEU_CAVEPARTICULIERE)->complement, 5, "L'ajout d'un mouvement complement génère un stock complement");
$t->is($registre->declaration->get($produit_hash)->details->get(RegistreVCIClient::LIEU_CAVEPARTICULIERE)->stock_final, 0, "L'ajout d'un mouvement impacte le stock du detail");
$t->is($registre->declaration->get($produit_hash)->complement, 5, "L'ajout du mouvement génère un stock complement au niveau produit");
$t->is($registre->declaration->get($produit_hash)->stock_final, 0, "L'ajout d'un mouvement impacte le stock du produit");
$t->is(count($registre->lignes), 5, 'a un mouvement');
$t->is($registre->lignes[4]->volume, 5, 'volume enregistré dans le mouvemnt');
$t->is($registre->lignes[4]->stock_resultant, 0, 'stock enregistré dans le mouvemnt');
$t->is($registre->lignes[4]->date, ($registre->campagne + 1).'-12-31', 'date du mouvement à la date de la fin de l\'année suivante');

$t->comment("Produits avec une chaine de caractere au lieu d'un noeud de la conf");
$registre->addLigne($produit_hash, RegistreVCIClient::MOUVEMENT_COMPLEMENT, 5, RegistreVCIClient::LIEU_CAVEPARTICULIERE);
$t->is($registre->declaration->get($produit_hash)->details->get(RegistreVCIClient::LIEU_CAVEPARTICULIERE)->stock_final, -5, "L'ajout d'un mouvement avec un hash impacte le stock du detail");

$t->comment("Ajout d'un mouvement stocké hors cave particulière");
$registre->addLigne($produit_hash, RegistreVCIClient::MOUVEMENT_CONSTITUE, 5, $registre->identifiant);
$t->is($registre->declaration->get($produit_hash)->details->get($registre->identifiant)->stock_final, 5, "L'ajout d'un mouvement dans un autre lieu impact le stock du detail");
$t->is($registre->declaration->get($produit_hash)->details->get(RegistreVCIClient::LIEU_CAVEPARTICULIERE)->stock_final, -5, "L'ajout du mouvement n'impacte le stock CAVE PARTICULIERE");
$t->is($registre->declaration->get($produit_hash)->stock_final, 0, "L'ajout de ce mouvement impacte le stock du produit");
$t->isnt($registre->lignes[6]->detail_libelle, 'Cave particulière', 'libelle détail pas cave particulière');


$t->comment("test configuration pour un produit normal");
$t->is($registre->declaration->get($produit_hash)->getLibelleComplet(), 'AOC Alsace blanc Chasselas', "Libellé du produit OK");
$t->is(get_class($registre->declaration->get($produit_hash)->getConfig()), 'ConfigurationCepage', "Pour un produit, on a accès à la configuration");
$t->is(get_class($registre->declaration->get($produit_hash)->getAppellation()), 'ConfigurationAppellation', "Pour les crémants, on a une vraie class appellation");


$t->comment("Ajout d'un crémant");
$hash_cremant = 'certification/genre/appellation_CREMANT/mention/lieu/couleur/cepage_PB';
$hash_resultat = 'certification/genre/appellation_CREMANT';
$registre->addLigne($hash_cremant, RegistreVCIClient::MOUVEMENT_CONSTITUE, 10, RegistreVCIClient::LIEU_CAVEPARTICULIERE);
$t->is($registre->declaration->exist($hash_resultat), true, "Ajout d'un mouvement crémant le met au niveau appellation");
$t->is($registre->declaration->exist($hash_cremant), false, "Ajout d'un mouvement crémant ne le met pas au niveau cepage");
$t->is($registre->declaration->get($hash_resultat)->getLibelleComplet(), 'AOC Crémant d\'Alsace', "Le libellé complet d'un crément ne rentre pas dans les détails");
$t->is(get_class($registre->declaration->get($hash_resultat)->getConfig()), 'ConfigurationAppellation', "Pour les crémants, on a accès à la configuration");
$t->is(get_class($registre->declaration->get($hash_resultat)->getAppellation()), 'ConfigurationAppellation', "Pour les crémants, on a une vraie class appellation");
$pseudoapp = $registre->getProduitsWithPseudoAppelations();
$t->is($pseudoapp[2]->getLibelle(), 'AOC Crémant d\'Alsace', "Bon pseudo produit pour le Crémant");
$t->is(count($pseudoapp), 3, "Pas de double pseudo produit pour le Crémant");
$t->is($registre->lignes[7]->produit_libelle, 'AOC Crémant d\'Alsace', 'libelle crémant du mouvement est OK');

$registre->superficies_facturables = 5;
$registre->save();

$t->comment("Génération des mouvements de facturation");

$registre->generateMouvements();
$registre->save();

$t->is(count($registre->mouvements->get($compteIdentifiant)), 1, "Le registre à 1 mouvement");
$mouvement = $registre->mouvements->get($compteIdentifiant)->getFirst();
$t->is($mouvement->categorie, "vci", "Le registre à 1 mouvement");
$t->is($mouvement->type_libelle, "ares (récolte ".$campagne.")", "Libellé du mouvement vci");
$t->is($mouvement->facturable, 1, "Le mouvement est facturable");
$t->is($mouvement->facture, 0, "Le mouvement n'est pas facturé");

$t->comment("Génération de la facture");

$templateFacture = TemplateFactureClient::getInstance()->find("TEMPLATE-FACTURE-AOC-".$campagne);
$dateFacturation = date('Y-m-d');

$f = FactureClient::getInstance()->createFactureByTemplate($templateFacture, $compte, $dateFacturation);
$f->save();

$t->ok($f->_rev, "La facture ".$f->_id." a une révision");
$t->is(count($f->lignes->vci->details), 1, "La ligne de facturation pour le VCI est présente");
