<?php

class DRevDegustationConseilForm extends acCouchdbObjectForm
{
    public function configure() {
       $this->setWidgets(array(
            'cuve_alsace' => new sfWidgetFormDate(array()),
            'cuve_vtsgn' => new sfWidgetFormChoice(array('choices' => $this->getVtsgnChoices())),
        ));

        $this->setValidators(array(
            'cuve_alsace' => new sfValidatorDate(array('required' => false)),
            'cuve_vtsgn' => new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->getVtsgnChoices()))),
        ));

        $this->widgetSchema['cuve_alsace']->setLabel('Semaine du');
        $this->widgetSchema['cuve_vtsgn']->setLabel('Période de prélévement');

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
}