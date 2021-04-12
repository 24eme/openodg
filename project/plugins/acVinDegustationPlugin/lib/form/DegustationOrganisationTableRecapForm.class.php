<?php

class DegustationOrganisationTableRecapForm extends acCouchdbObjectForm {

    public function configure() {

        foreach ($this->getObject()->lots as $lot) {
            $name = $this->getWidgetNameFromLot($lot);
            $this->setWidget($name , new bsWidgetFormChoice(array('choices' => $this->getTablesListe())));
            $this->setValidator($name, new sfValidatorChoice(array('choices' => array_keys($this->getTablesListe()),'required' => false)));
        }

        $this->widgetSchema->setNameFormat('tables[%s]');
    }

    public function getWidgetNameFromLot($lot){
      return 'numero_lot_'.preg_replace("|/lots/|", '', $lot->getHash());
    }

    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);

        foreach ($this->getObject()->lots as $lot) {
            $name = $this->getWidgetNameFromLot($lot);
            if(intval($values[$name])){
                $lot->numero_table = intval($values[$name]);
            }else if($values[$name] === Lot::TABLE_IGNORE){
                $lot = $this->getObject()->ignorerLot($lot);
            }else{
                $lot->numero_table = null;
            }
        }
    }

    protected function updateDefaultsFromObject() {
        $defaults = $this->getDefaults();
        foreach ($this->getObject()->lots as $lot) {
            if($lot->exist('numero_table') && $lot->numero_table){
              $defaults[$this->getWidgetNameFromLot($lot)] = "".$lot->numero_table;
            }
        }
        $this->setDefaults($defaults);
    }

    protected function getTablesListe(){
        $liste_tables = $this->getObject()->getTablesWithFreeLots();
        $max_liste = (count($liste_tables))? max(array_keys($liste_tables)) + 1 : 1;

        $liste_tables_choices = array("" => "");
        for ($i=1; $i <= $max_liste; $i++) {
            $liste_tables_choices["".$i] = "Table ".DegustationClient::getNumeroTableStr($i);
        }
        $liste_tables_choices[Lot::TABLE_IGNORE] = "Ignoré";
        return $liste_tables_choices;
    }

}
