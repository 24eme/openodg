<?php
include_partial('global/step', array('object' => $chgtDenom, 'etapes' => ChgtDenomEtapes::getInstance(), 'step' => $step, 'ajax' => isset($ajax) && $ajax));
