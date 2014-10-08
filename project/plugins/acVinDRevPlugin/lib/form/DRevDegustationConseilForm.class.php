<?php

class DRevDegustationConseilForm extends acCouchdbObjectForm
{
    public function configure() {

        if($this->getObject()->getDocument()->prelevements->exist(Drev::CUVE_ALSACE)) {
            $form_alsace = new DRevPrelevementForm($this->getObject()->getDocument()->prelevements->get(Drev::CUVE_ALSACE));
            $this->embedForm(Drev::CUVE_ALSACE, $form_alsace);
        }  

        if($this->getObject()->getDocument()->prelevements->exist(Drev::CUVE_VTSGN)) {
            $form_vtsgn = new DRevPrelevementForm($this->getObject()->getDocument()->prelevements->get(Drev::CUVE_VTSGN));
            $form_vtsgn->setWidget("date", new sfWidgetFormChoice(array('choices' => $this->getVtsgnChoices())));
            $form_vtsgn->setValidator("date", new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->getVtsgnChoices()))));
            $form_vtsgn->getWidget("date")->setLabel("Période de prélévement");

            $this->setWidget("vtsgn_demande", new sfWidgetFormInputCheckbox(array()));
            $this->setValidator("vtsgn_demande", new sfValidatorBoolean());

            $this->embedForm(Drev::CUVE_VTSGN, $form_vtsgn);
            $form_vtsgn->validatorSchema['date']->setMessage('required', 'La semaine de degustation est obligatoire.');
        }

        

        if(count($this->getObject()->getDocument()->getEtablissementObject()->chais) > 1) {
            $this->setWidget("chai", new sfWidgetFormChoice(array('choices' => $this->getChaiChoice())));    
            $this->setValidator("chai", new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->getChaiChoice()))));
        }

        $this->widgetSchema->setNameFormat('degustation_conseil[%s]');
    }

    public function getChaiChoice() {
        $choices = array();
        foreach($this->getObject()->getDocument()->getEtablissementObject()->chais as $chai) {
            $choices[$chai->getKey()] = sprintf("%s %s %s", $chai->adresse, $chai->code_postal, $chai->commune);
        }

        return $choices;
    }

    public function getVtsgnChoices() {
        
        return array(
                     '' => '',
                     '2014-04-01' => 'Avril',
                     '2014-06-01' => 'Juin',
                     '2014-08-01' => 'Octobre',
                     );
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        $this->setDefault('vtsgn_demande', 1);
    }

    public function processValues($values) {
        $values = parent::processValues($values);
        if(isset($values['vtsgn_demande']) && !$values['vtsgn_demande']) {
            $values[Drev::CUVE_VTSGN]['date'] = null;
        }

        return $values;
    }

    public function doUpdateObject($values) 
    {
        foreach ($this->getEmbeddedForms() as $key => $embedForm) {
            $embedForm->doUpdateObject($values[$key]);
        }

        if(isset($values["chai"])) {
            $this->getObject()->getDocument()->chais->set(DRev::CUVE, $this->getObject()->getDocument()->getEtablissementObject()->chais->get($values["chai"])->toArray(true, false));
        }
    }
}