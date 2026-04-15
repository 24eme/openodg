<?php use_helper('Lot') ?>
<?php
include_partial('global/step', array('object' => $prisedemousse, 'etapes' => PriseDeMousseEtapes::getInstance(), 'step' => $step, 'ajax' => isset($ajax) && $ajax));
