<?php
include_partial('global/step', array('object' => $transaction, 'etapes' => TransactionEtapes::getInstance(), 'step' => $step, 'ajax' => isset($ajax) && $ajax));
