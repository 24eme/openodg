<?php

class ChgtDenomLogementForm extends acCouchdbObjectForm
{
    public function configure() {
      $this->setWidget('numero_logement_operateur', new bsWidgetFormInput());
      $this->setValidator('numero_logement_operateur', new sfValidatorString(array('required' => false)));
      $this->widgetSchema->setNameFormat('chgt_denom_logement[%s]');
    }
}
