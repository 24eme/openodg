<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ParcellaireAppellationProduitsForm
 *
 * @author mathurin
 */
class ParcellaireAppellationProduitsForm extends sfForm {

    protected $parcelles;

    public function __construct($parcelles, $appellationKey, $defaults = array(), $options = array(), $CSRFSecret = null) {
        $this->parcelles = $parcelles;
        $this->appellationKey = $appellationKey;
        parent::__construct($defaults, $options, $CSRFSecret);
    }

    public function configure() {
        if (count($this->parcelles)) {
            foreach ($this->parcelles as $key => $parcelle) {
                $form = new ParcellaireAffectationAppellationParcelleForm($parcelle, $this->appellationKey);
                $this->embedForm($parcelle->getHashForKey(), $form);
            }
        }
    }

    public function doUpdateObject($values) {
        foreach ($this->getEmbeddedForms() as $key => $embedForm) {
            unset($values[$key]['_revision']);
            $embedForm->doUpdateObject($values[$key]);
        }
    }

}
