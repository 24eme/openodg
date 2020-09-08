<?php

class DegustationResultatsForm extends acCouchdbObjectForm {

  private $tableLots = null;
  private $numero_table = null;

  public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null)
  {
    $this->tableLots = $options['tableLots'];
    $this->numero_table = $options['numero_table'];

    parent::__construct($object, $options, $CSRFSecret);
  }

    public function configure() {

      foreach ($this->getObject()->lots as $lot) {
        $name = $this->getWidgetNameFromLot($lot);
        $this->setWidget($name , new WidgetFormInputCheckbox());
        $this->setValidator($name, new ValidatorBoolean());

      }
      $this->widgetSchema->setNameFormat('resultats[%s]');
    }

    public function getTableLots(){
      return $this->tableLots;
    }

    public function getWidgetNameFromLot($lot){
      return 'numero_lot_'.preg_replace("|/lots/|", '', $lot->getHash());
    }

    public function getLotNodeFromName($name){
      return $this->getObject()->get("/lots/".preg_replace("|numero_lot_|", '', $name));
    }

    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);
        foreach ($this->tableLots as $lot) {
          $name = $this->getWidgetNameFromLot($lot);
          if($values[$name]){
            $lot->setStatut(Lot::STATUT_CONFORME);
          }else {
            $lot->setStatut(Lot::STATUT_NON_CONFORME);
          
        }
      }
    }

    protected function updateDefaultsFromObject() {
        $defaults = $this->getDefaults();
        foreach ($this->getObject()->lots as $lot) {
            if(!$lot->isNonConforme()){
              $defaults[$this->getWidgetNameFromLot($lot)] = 1;
            }
        }
        $this->setDefaults($defaults);
    }


}
