<?php

class ControleManquementForm extends acCouchdbObjectForm
{
    public function configure()
    {
        $this->setWidget('observations', new sfWidgetFormTextarea());
        $this->validatorSchema['observations'] = new sfValidatorPass();

        $this->setWidget('manquementCheckbox', new sfWidgetFormInputCheckbox());
        $this->validatorSchema['manquementCheckbox'] = new sfValidatorBoolean();
    }
}
