<?php

class DegustationAffectionLotForm extends BaseForm
{

    public function __construct(Lot $lot)
    {
        $this->lot = $lot;

        parent::__construct();
    }
    public function configure() {

        $this->setWidget('degustation',new bsWidgetFormChoice( array('choices' => $this->getDegustationChoices()) ));
        $this->setValidator('degustation', new sfValidatorPass(array('required' => true)));

        $this->setWidget('preleve',new WidgetFormInputCheckbox());
        $this->setValidator('preleve', new ValidatorBoolean(array('required' => true)));

        $tables = array(1 => "Table A",2 => "Table B",3 => "Table C",4 => "Table D",5 => "Table E",6 => "Table F");
        $this->setWidget('numero_table' ,new bsWidgetFormChoice(array('choices' => $tables )) );
        $this->setValidator('numero_table', new sfValidatorPass(array('required' => false)));

        $this->validatorSchema->setPostValidator(new DegustationAffectationValidator($this));

        $this->widgetSchema->setNameFormat('degustation_affectation_lot[%s]');
    }

    public function getDegustationChoices() {
        $degustations = array();
        $degustationsEnCours = DegustationClient::getInstance()->getHistoryEncours();

        foreach ($degustationsEnCours as $degustation_id => $degustation) {
          // "Dégustation du ".format_date($degustation->date, "EEEE d MMMM yyyy", "fr_FR")." à ".format_date($degustation->date, 'HH')."h".format_date($degustation->date, 'mm')." au ".$degustation->lieu
          $degustations[$degustation_id] = "Degustation du ".$degustation->date." au ".$degustation->lieu;
        }
        return $degustations;
    }

    public function save() {
        $values = $this->getValues();
        $lot = $this->lot;
        $degustation = DegustationClient::getInstance()->find($values['degustation']);
        $lotsArray = $degustation->getLots();

        if (!$lot->getMouvement(Lot::STATUT_AFFECTABLE)) {

          throw new \Exception("Erreur : Ce lot n'est plus affectable.Numéro lot :".$lot->unique_id, 1);

        }

        $degustation->addLot($lot,true);

        $lot = $degustation->getLot($lot->unique_id);

        if ($values['preleve']){
          $lot->setIsPreleve();
        }

        $lot->setNumeroTable($values['numero_table']);

        $degustation->save();
        return $degustation;
    }
}
