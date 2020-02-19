<?php
include_partial('global/step', array('object' => $parcellaireAffectation, 'etapes' => ParcellaireIntentionAffectationEtapes::getInstance(), 'step' => $step, 'ajax' => isset($ajax) && $ajax));
