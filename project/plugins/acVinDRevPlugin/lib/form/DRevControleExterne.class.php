<?php

class DRevControleExterneForm extends acCouchdbObjectForm
{
    public function configure() {
       $this->setWidgets(array(
            'bouteille_alsace' => new sfWidgetFormDate(array()),
            'bouteille_alsace_grdcru' => new sfWidgetFormDate(array()),
            'bouteille_vtsgn' => new sfWidgetFormDate(array()),
        ));

        $this->setValidators(array(
            'bouteille_alsace' => new sfValidatorDate(array('required' => false)),
            'bouteille_alsace_grdcru' => new sfValidatorDate(array('required' => false)),
            'bouteille_vtsgn' => new sfValidatorDate(array('required' => false)),
        ));

        $this->widgetSchema['bouteille_alsace']->setLabel('Semaine du');
        $this->widgetSchema['bouteille_alsace_grdcru']->setLabel('Semaine du');
        $this->widgetSchema['bouteille_vtsgn']->setLabel('Semaine du');

        $this->widgetSchema->setNameFormat('controle_externe[%s]');
    }
}