<?php

class DegustationDegustateursTableForm extends acCouchdbObjectForm {

  private $numero_table = null;

  public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null)
  {
    $this->numero_table = $options['numero_table'];

    parent::__construct($object, $options, $CSRFSecret);
  }

  public function configure() {
      foreach ($this->getDegustateursForTable() as $key => $degustateur) {
        $this->setWidget($key , new WidgetFormInputCheckbox());
        $this->setValidator($key, new ValidatorBoolean());
    }
    $this->widgetSchema->setNameFormat('degustateurs_table[%s]');
  }

  protected function doUpdateObject($values) {
    parent::doUpdateObject($values);
    foreach ($this->getDegustateursForTable() as $key => $degustateur) {
      if($values[$key]){
        if($degustateur->confirmation === false){
          $degustateur->confirmation = true;
        }
        $degustateur->add('numero_table',$this->numero_table);
      }elseif ($degustateur->exist('numero_table') && ($degustateur->numero_table == $this->numero_table)) {
        $degustateur->remove("numero_table");
      }
    }
  }

  protected function updateDefaultsFromObject() {
    $defaults = $this->getDefaults();
    foreach ($this->getDegustateursForTable() as $key => $degustateur) {
        if($degustateur->exist('numero_table') && ($this->numero_table == $degustateur->get("numero_table"))){
          $defaults[$key] = 1;
        }
      }

    $this->setDefaults($defaults);
  }

  public function getDegustateursForTable(){
    $degustateursDispo = array();

    foreach ($this->getObject()->getAllDegustateurs() as $key => $degustateur) {
      if($degustateur->exist('numero_table') && $degustateur->numero_table != $this->numero_table){
        continue;
      }
      $degustateursDispo[$key] = $degustateur;
    }
    return $degustateursDispo;
  }

}
