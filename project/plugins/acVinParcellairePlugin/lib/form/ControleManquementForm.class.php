<?php

class ControleManquementForm extends acCouchdbObjectForm
{
    public function configure()
    {
        $this->setWidget('observations', new sfWidgetFormTextarea());
        $this->validatorSchema['observations'] = new sfValidatorPass();

        $this->setWidget('manquement_checkbox', new sfWidgetFormInputCheckbox());
        $this->validatorSchema['manquement_checkbox'] = new sfValidatorBoolean();
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();

        if ($this->getObject()->getDocument()->manquements->exist($this->getObject()->getKey())) {
            $this->setDefault('manquement_checkbox', true);
        }
    }
}
