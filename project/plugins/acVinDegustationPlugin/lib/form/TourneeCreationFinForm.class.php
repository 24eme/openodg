<?php

class TourneeCreationFinForm extends acCouchdbObjectForm
{
    public function configure() {
        $this->setWidget('date_prelevement_debut', new sfWidgetFormInput(array(), array()));
        $this->setValidator('date_prelevement_debut', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));

        $this->setWidget('nombre_operateurs_a_prelever', new sfWidgetFormInput());
        $this->setValidator('nombre_operateurs_a_prelever', new sfValidatorInteger());

        $this->setWidget('heure', new sfWidgetFormInput(array(), array()));
        $this->setValidator('heure', new sfValidatorTime(array('time_output' => 'H:i', 'time_format' => '~(?P<hour>\d{2}):(?P<minute>\d{2})~', 'required' => true)));

        $this->setWidget('lieu', new sfWidgetFormInput());
        $this->setValidator('lieu', new sfValidatorString());

        $this->widgetSchema->setNameFormat('tournee_creation_fin[%s]');
    }

}