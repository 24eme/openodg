<?php

class DegustationAffectionLotForm extends BaseForm
{
    public function __construct(Lot $lot,Degustation $degust)
    {
        $this->lot = $lot;
        $this->degustation = $degust;

        parent::__construct();
    }
    public function configure() {

        $this->setWidget('preleve',new WidgetFormInputCheckbox());
        $this->setValidator('preleve', new ValidatorBoolean());

        $this->setWidget('table' , new bsWidgetFormInput());
        $this->setValidator('table', new sfValidatorNumber());

        $this->widgetSchema->setNameFormat('degustation_affectation_lot[%s]');
    }

    public function save() {
        $values = $this->getValues();

        $degustation = $this->degustation;
        $degustation->save();

        return $degustation;
    }
}
