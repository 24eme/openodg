<?php
include_partial('global/step', array('object' => $chgdenom, 'etapes' => ChgtDenomEtapes::getInstance(), 'step' => $step, 'ajax' => isset($ajax) && $ajax));
