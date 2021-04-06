<?php
include_partial('global/step', array('object' => $conditionnement, 'etapes' => ConditionnementEtapes::getInstance(), 'step' => $step, 'ajax' => isset($ajax) && $ajax));
