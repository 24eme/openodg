<?php
include_partial('global/step', array('object' => $parcellaire, 'etapes' => ParcellaireAffectationEtapes::getInstance(), 'step' => $step, 'routeparams' => array("parcellaire_parcelles" => array('sf_subject' => $parcellaire, 'appellation' => ParcellaireAffectationClient::getInstance()->getFirstAppellation($parcellaire->getTypeParcellaire())))));
