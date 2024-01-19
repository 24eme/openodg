<?php
include_partial('global/step', array('object' => $adelphe, 'etapes' => AdelpheEtapes::getInstance(), 'step' => $step, 'ajax' => isset($ajax) && $ajax));
