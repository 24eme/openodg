<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(3);

$t->is(Phone::clean("+331 42.70-28  67"), "0142702867", "Nettoyage d'un numéro de téléphone en supprimant tous les caractères autre qu'un chiffre");
$t->is(Phone::format("+331 42.70-28  67"), "01 42 70 28 67", "Formatage d'un numéro de téléphone avec un espace tous les 2 nombres");
$t->is(Phone::format("coucou"), null, "Un numéro de téléphone vide renvoie null");
