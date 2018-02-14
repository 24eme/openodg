<?php
include_partial('global/step', array('object' => $parcellaireIrrigable, 'etapes' => ParcellaireIrrigableEtapes::getInstance(), 'step' => $step, 'ajax' => isset($ajax) && $ajax));
