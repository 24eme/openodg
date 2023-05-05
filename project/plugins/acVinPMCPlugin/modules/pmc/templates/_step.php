<?php
include_partial('global/step', array('object' => $pmc, 'etapes' => PMCEtapes::getInstance(), 'step' => $step, 'ajax' => isset($ajax) && $ajax));
