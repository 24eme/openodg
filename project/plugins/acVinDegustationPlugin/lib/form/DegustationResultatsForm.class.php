<?php

class DegustationResultatsForm extends acCouchdbObjectForm {

  private $numero_table = null;

  public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null)
  {
    $this->numero_table = $options['numero_table'];

    parent::__construct($object, $options, $CSRFSecret);
  }

    public function configure() {


      foreach ($this->getTableLots() as $lot) {

        $name = $this->getWidgetNameFromLot($lot);
        $this->setWidget('conformite_'.$name , new sfWidgetFormChoice(array('choices' => $this->getConformites())));
        $this->widgetSchema['conformite_'.$name]->setLabel("Conformité de l'échantillon :");
        $this->setValidator('conformite_'.$name, new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getConformites())),array('required' => "Aucune conformite saisie.")));

        $this->setWidget('motif_'.$name , new sfWidgetFormTextarea());
        $this->widgetSchema['motif_'.$name]->setLabel("Motif de conformité/non conformité de l'échantillon :");
        $this->setValidator('motif_'.$name, new sfValidatorString(array('required' => false)));

        $this->setWidget('observation_'.$name , new sfWidgetFormTextarea());
        $this->widgetSchema['observation_'.$name]->setLabel("Observation :");
        $this->setValidator('observation_'.$name, new sfValidatorString(array('required' => false)));
      }
      $this->widgetSchema->setNameFormat('resultats[%s]');
    }

    public function getTableLots(){
      return $this->getObject()->getLotsByTable($this->numero_table);
    }

    public function getWidgetNameFromLot($lot){
      return preg_replace("|/lots/|", '', $lot->getHash());
    }

    public function getLotNodeFromName($name){
      return $this->getObject()->get("/lots/".preg_replace("|numero_lot_|", '', $name));
    }

    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);

        foreach ($this->getTableLots() as $lot) {
          $name = $this->getWidgetNameFromLot($lot);
          $lot->setConformiteLot($values['conformite_'.$name], $values['motif_'.$name], $values['observation_'.$name]);
        }
    }

    protected function updateDefaultsFromObject() {
        $defaults = $this->getDefaults();
        foreach ($this->getTableLots() as $lot) {
            $name = $this->getWidgetNameFromLot($lot);
            $defaults['conformite_'.$name] = $lot->getConformite();
            $defaults['motif_'.$name] = $lot->getMotif();
            $defaults['observation_'.$name] = $lot->getObservation();
        }
        $this->setDefaults($defaults);
    }

    protected function getConformites(){

      return Lot::$libellesConformites;
    }

    protected function doSave($con = NULL) {
        $this->updateObject();
        $this->object->getCouchdbDocument()->save(false);
    }

}
