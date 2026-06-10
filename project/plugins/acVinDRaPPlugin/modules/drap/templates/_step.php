<?php
include_partial('global/step', array('object' => $drap, 'etapes' => DRaPEtapes::getInstance(), 'step' => $step, 'ajax' => isset($ajax) && $ajax));
