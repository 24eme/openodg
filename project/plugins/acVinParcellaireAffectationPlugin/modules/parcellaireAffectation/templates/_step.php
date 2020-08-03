<?php
include_partial('global/step', array('object' => $parcellaireAffectation, 'etapes' => ParcellaireAffectationEtapes::getInstance(), 'step' => $step, 'ajax' => isset($ajax) && $ajax));
