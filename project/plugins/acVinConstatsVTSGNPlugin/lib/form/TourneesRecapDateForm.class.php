<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TourneesRecapDateForm
 *
 * @author mathurin
 */
class TourneesRecapDateForm extends sfForm {

    public function __construct($defaults = array(), $options = array(), $CSRFSecret = null) {
        parent::__construct($defaults, $options, $CSRFSecret);
    }

   public function configure() {
        $this->setWidget('date', new sfWidgetFormInput(array(), array()));

        $this->widgetSchema->setLabel('date', 'Date');

        $this->setValidator('date', new sfValidatorDate(
                array('date_output' => 'Y-m-d',
            'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~',
            'required' => true)));

        $this->widgetSchema->setNameFormat('tourneesRecapDate[%s]');
    }

}
