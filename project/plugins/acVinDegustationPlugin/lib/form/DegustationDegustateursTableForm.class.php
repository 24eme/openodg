<?php

class DegustationDegustateursTableForm extends acCouchdbObjectForm {

  private $numero_table = null;

  public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null)
  {
    $this->numero_table = $options['numero_table'];

    parent::__construct($object, $options, $CSRFSecret);
  }

  public function configure() {
      foreach ($this->getObject()->getDegustateursConfirmes() as $degustateur) {
        $name = $this->getWidgetNameFromDegustateur($degustateur);
        $this->setWidget($name , new WidgetFormInputCheckbox());
        $this->setValidator($name, new ValidatorBoolean());
    }
    $this->widgetSchema->setNameFormat('degustateurs_table[%s]');
  }



  public function getWidgetNameFromDegustateur($degustateur){
    return 'numero_table_'.preg_replace("|/degustateurs/|", '', $degustateur->getHash());
  }

  public function getDegustateurNodeFromName($name){
    var_dump($name); exit;
    return $this->getObject()->get("/degustateurs/".preg_replace("|numero_table_|", '', $name));
  }

  protected function doUpdateObject($values) {
    parent::doUpdateObject($values);
    foreach ($this->getObject()->getDegustateursConfirmes() as $degustateur) {
      $name = $this->getWidgetNameFromDegustateur($degustateur);
      if($values[$name]){
        $degustateur->add('numero_table',$this->numero_table);
      }elseif ($degustateur->exist('numero_table') && ($degustateur->numero_table == $this->numero_table)) {
        $degustateur->numero_table = null;
      }
    }
  }

  protected function updateDefaultsFromObject() {
    $defaults = $this->getDefaults();
    foreach ($this->getObject()->getDegustateursConfirmes() as $degustateur) {
        if($degustateur->exist('numero_table') && ($this->numero_table == $degustateur->get("numero_table"))){
          $defaults[$this->getWidgetNameFromDegustateur($degustateur)] = 1;
        }
      }

    $this->setDefaults($defaults);
  }


}
