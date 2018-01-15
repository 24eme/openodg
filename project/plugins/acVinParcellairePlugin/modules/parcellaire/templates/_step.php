<?php
include_partial('global/step', array('object' => $parcellaire, 'etapes' => ParcellaireEtapes::getInstance(), 'step' => $step, 'routeparams' => array("parcellaire_parcelles" => array('sf_subject' => $parcellaire, 'appellation' => ParcellaireClient::getInstance()->getFirstAppellation($parcellaire->getTypeParcellaire())))));
