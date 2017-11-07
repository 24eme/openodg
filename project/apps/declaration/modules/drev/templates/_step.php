<?php
include_partial('global/step', array('object' => $drev, 'etapes' => DrevEtapes::getInstance(), 'step' => $step, 'ajax' => isset($ajax) && $ajax));
