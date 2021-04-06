<?php

class DegustationDegustateursConfirmationForm extends acCouchdbObjectForm {


  public function configure() {
    foreach ($this->getObject()->getDegustateurs() as $degustateurs) {
      foreach ($degustateurs as $id => $degustateur){
        $name = $this->getWidgetNameFromDegustateur($degustateur);
        $this->setWidget($name , new WidgetFormInputCheckbox());
        $this->setValidator($name, new ValidatorBoolean());
      }
    }
    $this->widgetSchema->setNameFormat('degustateurs_table[%s]');
  }



  public function getWidgetNameFromDegustateur($degustateur){
    return 'confirmation_'.preg_replace("|/degustateurs/|", '', $degustateur->getHash());
  }

  public function getDegustateurNodeFromName($name){
    return $this->getObject()->get("/degustateurs/".preg_replace("|confirmation_|", '', $name));
  }

  protected function doUpdateObject($values) {
    parent::doUpdateObject($values);
    foreach ($this->getObject()->getDegustateurs() as $degustateurs) {
      foreach ($degustateurs as $id => $degustateur){
        $name = $this->getWidgetNameFromDegustateur($degustateur);
        if($values[$name]){
          $degustateur->add('confirmation',boolval($values[$name]));
        }elseif($degustateur->exist('confirmation') && $degustateur->confirmation && !$values[$name]){
          $degustateur->remove('confirmation');
        }
      }
    }
  }

  protected function updateDefaultsFromObject() {
    $defaults = $this->getDefaults();
    foreach ($this->getObject()->getDegustateurs() as $degustateurs) {
      foreach ($degustateurs as $id => $degustateur){
        if($degustateur->exist('confirmation') && $degustateur->get("confirmation")){
          $defaults[$this->getWidgetNameFromDegustateur($degustateur)] = 1;
        }
      }
    }
    $this->setDefaults($defaults);
  }

  protected function doSave($con = null) {
      $this->updateObject();
      $this->object->getCouchdbDocument()->save(false);
  }

}
