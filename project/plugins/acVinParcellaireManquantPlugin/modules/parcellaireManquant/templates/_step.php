<?php
include_partial('global/step', array('object' => $parcellaireManquant, 'etapes' => ParcellaireManquantEtapes::getInstance(), 'step' => $step, 'ajax' => isset($ajax) && $ajax));
