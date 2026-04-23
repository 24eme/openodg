<?php
include_partial('global/step', array('object' => $parcellaireIrrigable, 'etapes' => DRapEtapes::getInstance(), 'step' => $step, 'ajax' => isset($ajax) && $ajax));
