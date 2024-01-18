<?php
class AdelpheRepartitionForm extends acCouchdbObjectForm {

  public function configure() {
    $this->widgetSchema->setNameFormat('adelphe[%s]');
  }
}
