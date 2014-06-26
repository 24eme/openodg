<?php

class DRevDegustationConseilForm extends acCouchdbObjectForm
{
    public function configure() {
        $form_alsace = new DRevPrelevementForm($this->getObject()->getDocument()->addPrelevement(Drev::CUVE_ALSACE));
        
        $form_vtsgn = new DRevPrelevementForm($this->getObject()->getDocument()->addPrelevement(Drev::CUVE_VTSGN));
        $form_vtsgn->setWidget("date", new sfWidgetFormChoice(array('choices' => $this->getVtsgnChoices())));
        $form_vtsgn->setValidator("date", new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->getVtsgnChoices()))));
        $form_vtsgn->getWidget("date")->setLabel("Période de prélévement");

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

    public function doUpdateObject($values) 
    {
        foreach ($this->getEmbeddedForms() as $key => $embedForm) {
            $embedForm->doUpdateObject($values[$key]);
        }
    }
}