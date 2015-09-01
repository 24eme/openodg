<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RendezvousDeclarantForm
 *
 * @author mathurin
 */
class RendezvousDeclarantForm extends sfForm {

    private $chaiKey = null;

    public function __construct($chaiKey, $defaults = array(), $options = array(), $CSRFSecret = null) {
        $this->chaiKey = $chaiKey;
        parent::__construct($defaults, $options, $CSRFSecret);
    }

    public function configure() {
        $this->setWidget('date', new sfWidgetFormInput(array(), array()));
        $this->setWidget('heure', new sfWidgetFormInput(array(), array()));
        $this->setWidget('commentaire', new sfWidgetFormTextarea());

        $this->widgetSchema->setLabel('date', 'Date');
        $this->widgetSchema->setLabel('heure', 'Heure');
        $this->widgetSchema->setLabel('commentaire', 'Commentaire');


        $this->setValidator('date', new sfValidatorDate(
                array('date_output' => 'Y-m-d',
            'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~',
            'required' => true)));
        $this->setValidator('heure', new sfValidatorTime(
                array('time_output' => 'H:i',
            'time_format' => '~(?P<hour>\d{2}):(?P<minute>\d{2})~',
            'required' => true)
        ));
        $this->setValidator('commentaire', new sfValidatorString(array("required" => false)));


        $this->widgetSchema->setNameFormat('rendezvous_declarant_' . $this->chaiKey . '[%s]');
    }


}
