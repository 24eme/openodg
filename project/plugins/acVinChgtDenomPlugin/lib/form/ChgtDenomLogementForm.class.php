<?php

class ChgtDenomLogementForm extends acCouchdbObjectForm
{
    public function configure() {
      $this->setWidget('numero_cuve', new bsWidgetFormInput());
      $this->setValidator('numero_cuve', new sfValidatorString(array('required' => false)));
      $this->widgetSchema->setNameFormat('chgt_denom_logement[%s]');
    }
}
