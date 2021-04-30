<?php

class ChgtDenomLogementForm extends acCouchdbObjectForm
{
    public function configure() {
      $this->setWidget('changement_numero_logement_operateur', new bsWidgetFormInput());
      $this->setValidator('changement_numero_logement_operateur', new sfValidatorString());
      $this->widgetSchema->setNameFormat('chgt_denom_logement[%s]');
    }
}
