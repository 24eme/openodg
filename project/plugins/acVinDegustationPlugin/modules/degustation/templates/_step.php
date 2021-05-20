<?php
$etapes =  DegustationEtapes::getInstance();
$active = ($active) ? $active : $etapes->getFirst();

include_partial('global/step', array('object' => $degustation, 'etapes' => $etapes, 'step' => $active));

include_partial('global/flash');
