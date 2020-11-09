<?php

class ChgtDenomLogementForm extends acCouchdbObjectForm
{
    public function configure() {
      $this->setWidget('numero', new bsWidgetFormInput());
      $this->setValidator('numero', new sfValidatorString(array('required' => false)));
      $this->widgetSchema->setNameFormat('chgt_denom_logement[%s]');
    }
}
