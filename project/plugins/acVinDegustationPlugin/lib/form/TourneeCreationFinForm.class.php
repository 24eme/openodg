<?php

class TourneeCreationFinForm extends acCouchdbObjectForm
{
    public function configure() {
        $this->setWidget('nombre_operateurs_a_prelever', new sfWidgetFormInput());
        $this->setValidator('nombre_operateurs_a_prelever', new sfValidatorInteger());

        $this->setWidget('nombre_commissions', new sfWidgetFormInput());
        $this->setValidator('nombre_commissions', new sfValidatorInteger());

        $this->setWidget('heure', new sfWidgetFormInput(array(), array()));
        $this->setValidator('heure', new sfValidatorTime(array('time_output' => 'H:i', 'time_format' => '~(?P<hour>\d{2}):(?P<minute>\d{2})~', 'required' => true)));

        $this->setWidget('lieu', new sfWidgetFormInput());
        $this->setValidator('lieu', new sfValidatorString());

        $this->widgetSchema->setNameFormat('tournee_creation_fin[%s]');
    }

}