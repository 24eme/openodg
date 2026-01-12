<?php

class ControleManquementForm extends acCouchdbObjectForm
{
    public function configure()
    {
        // $this->setWidget('observations', new bsWidgetFormTextarea());
        $this->setWidget('observations', new sfWidgetFormTextarea());
        $this->validatorSchema['observations'] = new sfValidatorPass();
        $this->widgetSchema->setNameFormat('updateObservations[%s]');
        // $this->setWidget('conseils', new bsWidgetFormTextarea());
        // $this->setWidget('delais', new sfWidgetFormInputText());

        // $this->getWidget('observations')->setLabel("Observations:");
        // $this->getWidget('conseils')->setLabel("Conseils:");
        // $this->getWidget('delais')->setLabel("Délais:");

        // $this->setValidator('observations', new sfValidatorString(array('required' => false)));
        // $this->setValidator('conseils', new sfValidatorString(array('required' => false)));
        // $this->setValidator('delais', new sfValidatorString(array('required' => false)));
    }
}
