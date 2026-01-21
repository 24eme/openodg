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

        $objetKey = $this->getObject()->getKey();
        if ($this->getObject()->getDocument()->manquements->exist($objetKey)) {
            $this->setDefault('manquement_checkbox', $this->getObject()->getDocument()->manquements->$objetKey->actif);
        }
    }
}
