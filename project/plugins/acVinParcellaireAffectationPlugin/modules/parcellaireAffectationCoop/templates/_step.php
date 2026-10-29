<?php
include_partial('global/step', array('object' => $parcellaireAffectationCoop, 'etapes' => ParcellaireAffectationCoopEtapes::getInstance(), 'step' => $step, 'ajax' => isset($ajax) && $ajax));
