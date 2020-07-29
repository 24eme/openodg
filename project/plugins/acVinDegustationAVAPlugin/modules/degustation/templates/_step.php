<?php
$etapes =  TourneeEtapes::getInstance();
$active = ($active) ? $active : $etapes->getFirst();

include_partial('global/step', array('object' => $tournee, 'etapes' => $etapes, 'step' => $active));
