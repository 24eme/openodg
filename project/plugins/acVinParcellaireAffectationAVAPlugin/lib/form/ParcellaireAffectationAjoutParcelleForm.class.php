<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ParcellaireAffectationAjoutParcelleForm
 *
 * @author mathurin
 */
class ParcellaireAffectationAjoutParcelleForm extends ParcellaireAffectationParcelleForm {

    protected $appellationKey;

    public function __construct(acCouchdbJson $object, $appellationKey, $options = array(), $CSRFSecret = null) {
        $this->appellationKey = $appellationKey;
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure() {
        parent::configure();

        $this->widgetSchema->setNameFormat('parcellaire_ajout_parcelle[%s]');
    }

    public function getAppellationNode() {
		if ($this->appellationKey == ParcellaireAffectationClient::APPELLATION_VTSGN) {
			return $this->getObject()->getAppellationNodeFromAppellationKey(ParcellaireAffectationClient::APPELLATION_ALSACEBLANC, true);
		}
        return $this->getObject()->getAppellationNodeFromAppellationKey($this->appellationKey, true);
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
    }
    

}
