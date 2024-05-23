<?php

class ParcellaireAffectationCoopSaisieForm extends ParcellaireAffectationProduitsForm {

    public function __construct($affectationParcellaire, $coop)
    {
        parent::__construct($affectationParcellaire);
    }

    public function configure() {
		parent::configure();
        $this->setWidget('observations',new bsWidgetFormTextarea(array(), array('style' => 'width: 100%;resize:none;')));
        $this->setValidator('observations',new sfValidatorString(array('required' => false)));
    }


    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }

}
