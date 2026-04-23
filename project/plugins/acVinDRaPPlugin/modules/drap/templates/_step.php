<?php
include_partial('global/step', array('object' => $parcellaireIrrigable, 'etapes' => DRaPEtapes::getInstance(), 'step' => $step, 'ajax' => isset($ajax) && $ajax));
