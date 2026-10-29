<?php
class AdelpheVolumeForm extends acCouchdbObjectForm {

  public function configure() {
    $this->setWidget('volume_conditionne_total', new bsWidgetFormInputFloat());
    $this->setValidator('volume_conditionne_total', new sfValidatorNumber());
    $this->widgetSchema->setNameFormat('adelphe[%s]');
  }
}
