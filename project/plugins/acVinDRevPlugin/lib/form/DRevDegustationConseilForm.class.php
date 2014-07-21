<?php

class DRevDegustationConseilForm extends acCouchdbObjectForm
{
    public function configure() {
        $form_alsace = new DRevPrelevementForm($this->getObject()->getDocument()->addPrelevement(Drev::CUVE_ALSACE));
        

        $form_vtsgn = new DRevPrelevementForm($this->getObject()->getDocument()->addPrelevement(Drev::CUVE_VTSGN));
        $form_vtsgn->setWidget("date", new sfWidgetFormChoice(array('choices' => $this->getVtsgnChoices())));
        $form_vtsgn->setValidator("date", new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->getVtsgnChoices()))));
        $form_vtsgn->getWidget("date")->setLabel("Période de prélévement");

        $this->setWidget("vtsgn_demande", new sfWidgetFormInputCheckbox(array()));
        $this->setValidator("vtsgn_demande", new sfValidatorBoolean());

        $this->embedForm(Drev::CUVE_ALSACE, $form_alsace);
        $this->embedForm(Drev::CUVE_VTSGN, $form_vtsgn);

        $this->widgetSchema->setNameFormat('degustation_conseil[%s]');
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
        if(!$values['vtsgn_demande']) {
            $values[Drev::CUVE_VTSGN]['date'] = null;
        }

        return $values;
    }

    public function doUpdateObject($values) 
    {
        foreach ($this->getEmbeddedForms() as $key => $embedForm) {
            $embedForm->doUpdateObject($values[$key]);
        }
    }
}