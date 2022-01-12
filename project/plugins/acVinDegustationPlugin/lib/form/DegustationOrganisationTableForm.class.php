<?php

class DegustationOrganisationTableForm extends acCouchdbObjectForm {

  private $tableLots = null;
  private $numero_table = null;

  public function __construct(acCouchdbJson $object, $numero_table, $options = array(), $CSRFSecret = null)
  {
    $this->numero_table = $numero_table;
    $this->tableLots = $object->getLotsTableOrFreeLotsCustomSort($this->numero_table);
    parent::__construct($object, $options, $CSRFSecret);
  }

    public function configure() {
        foreach ($this->tableLots as $lot) {
          $name = $this->getWidgetNameFromLot($lot);
          $this->setWidget($name , new WidgetFormInputCheckbox());
          $this->setValidator($name, new ValidatorBoolean());
        }

        $this->widgetSchema->setNameFormat('tables[%s]');
    }

    public function getTableLots(){
      return $this->tableLots;
    }

    public function getWidgetNameFromLot($lot){
        if ($lot->isLeurre()) {
            return 'lot_leure-'.$lot->getKey();
        }
        return 'lot_'.$lot->declarant_identifiant."-".$lot->unique_id;
    }

    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);
        foreach ($this->tableLots as $lot) {
          $name = $this->getWidgetNameFromLot($lot);
          if($values[$name]){
            $lot->numero_table = $this->numero_table;
          }elseif ($lot->exist('numero_table') && ($lot->numero_table == $this->numero_table)) {
            $lot->numero_table = null;
          }
        }

    }

    protected function updateDefaultsFromObject() {
        $defaults = $this->getDefaults();
        foreach ($this->getObject()->lots as $lot) {
            if($lot->exist('numero_table') && ($lot->numero_table == $this->numero_table)){
              $defaults[$this->getWidgetNameFromLot($lot)] = 1;
            }
        }
        $this->setDefaults($defaults);
    }


    protected function doSave($con = null) {
        $this->updateObject();
        $this->object->getCouchdbDocument()->save(false);
    }


}

