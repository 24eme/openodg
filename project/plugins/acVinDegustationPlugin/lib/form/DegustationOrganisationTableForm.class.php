<?php

class DegustationOrganisationTableForm extends acCouchdbObjectForm {

  private $tableLots = null;
  private $numero_table = null;
  private $liste_tables = null;

  public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null)
  {
    $this->tableLots = $options['tableLots'];
    $this->numero_table = $options['numero_table'];

    if(!$this->numero_table){
      $this->liste_tables = $options['liste_tables'];
    }

    parent::__construct($object, $options, $CSRFSecret);
  }

    public function configure() {

      if(!$this->numero_table){
        foreach ($this->getObject()->lots as $lot) {
          $name = $this->getWidgetNameFromLot($lot);
          $this->setWidget($name , new bsWidgetFormChoice(array('choices' => $this->getTablesListe())));
          $this->setValidator($name, new sfValidatorChoice(array('choices' => array_keys($this->getTablesListe()),'required' => false)));
        }
      }else{

        foreach ($this->tableLots as $lot) {
          $name = $this->getWidgetNameFromLot($lot);
          $this->setWidget($name , new WidgetFormInputCheckbox());
          $this->setValidator($name, new ValidatorBoolean());
        }
      }
      $this->widgetSchema->setNameFormat('tables[%s]');
    }

    public function getNumeroTable(){
      return $this->numero_table + 1;
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

        if(!$this->numero_table){
          foreach ($this->getObject()->lots as $lot) {
              $name = $this->getWidgetNameFromLot($lot);
              if(intval($values[$name])){
                $lot->numero_table = intval($values[$name]);
              }else{
                $lot->numero_table = null;
              }
          }
        }else{
                    foreach ($this->tableLots as $lot) {
          $name = $this->getWidgetNameFromLot($lot);
          if($values[$name]){
            $lot->numero_table = $this->numero_table;
          }elseif ($lot->exist('numero_table') && ($lot->numero_table == $this->numero_table)) {
            $lot->numero_table = null;
          }
        }
      }
    }

    protected function updateDefaultsFromObject() {
        $defaults = $this->getDefaults();
        foreach ($this->getObject()->lots as $lot) {
            if($lot->exist('numero_table') && ($lot->numero_table == $this->numero_table) && $this->numero_table){
              $defaults[$this->getWidgetNameFromLot($lot)] = 1;
            }
            if($lot->exist('numero_table') && $lot->numero_table && !$this->numero_table){
              $defaults[$this->getWidgetNameFromLot($lot)] = "".$lot->numero_table;
            }
        }
        $this->setDefaults($defaults);
    }

    protected function getTablesListe(){
      $max_liste = (count($this->liste_tables))? max(array_keys($this->liste_tables)) + 1 : 1;

      $liste_tables_choices = array("" => "");
      for ($i=1; $i <= $max_liste; $i++) {
        $liste_tables_choices["".$i] = "Table ".$i;
      }
      return $liste_tables_choices;
    }

}
