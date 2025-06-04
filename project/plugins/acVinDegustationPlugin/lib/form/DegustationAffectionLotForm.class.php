<?php

class DegustationAffectionLotForm extends BaseForm
{

    public function __construct(Lot $lot, $que_desgustation_en_cours = true)
    {
        $this->lot = $lot;
        $this->en_cours = $que_desgustation_en_cours;
        parent::__construct();
    }
    public function configure() {

        $this->setWidget('degustation',new bsWidgetFormChoice( array('choices' => $this->getDegustationChoices()) ));
        $this->setValidator('degustation', new sfValidatorPass(array('required' => true)));

        $this->setWidget('preleve',new WidgetFormInputCheckbox());
        $this->setValidator('preleve', new ValidatorBoolean());

        $tables = array(0 =>"Aucune",1=>"Table A",2=>"Table B",3=>"Table C",4=>"Table D",5=>"Table E",6=>"Table F",7=>"Table G",8=>"Table H",9 => "Table I",10=> "Table J");
        $this->setWidget('numero_table' ,new bsWidgetFormChoice(array('choices' => $tables )) );
        $this->setValidator('numero_table', new sfValidatorPass());

        $this->validatorSchema->setPostValidator(new DegustationAffectationValidator($this));

        $this->widgetSchema->setNameFormat('degustation_affectation_lot[%s]');
    }

    public function getDegustationChoices() {
        $degustations = array();
        if ($this->en_cours) {
            $listeDegustations = DegustationClient::getInstance()->getHistory(10, "", acCouchdbClient::HYDRATE_JSON, Organisme::getCurrentRegion());
        }else{
            $listeDegustations = DegustationClient::getInstance()->getHistory(100);
        }

        foreach ($listeDegustations as $degustation_id => $degustation) {
          $degustations[$degustation->_id] = "Degustation du ".$degustation->date." au ".$degustation->lieu;
        }
        return $degustations;
    }
    public function getDegustation() {
      $values = $this->getValues();
      $degustation = DegustationClient::getInstance()->find($values['degustation']);
      return $degustation;

    }

    public function save() {
        $values = $this->getValues();
        $lot = $this->lot;
        $degustation = DegustationClient::getInstance()->find($values['degustation']);

        if (!$lot->getMouvement(Lot::STATUT_AFFECTABLE) && !$lot->getMouvement(Lot::STATUT_RECOURS_OC)) {
          throw new \Exception("Erreur : Ce lot n'est plus affectable.Numéro lot :".$lot->unique_id, 1);
        }

        $degustation->addLot($lot,true);

        $lot = $degustation->getLot($lot->unique_id);
        $lot->statut = Lot::STATUT_ATTENTE_PRELEVEMENT;
        if ($values['preleve']  && in_array($degustation->etape,array(DegustationEtapes::ETAPE_PRELEVEMENTS,DegustationEtapes::ETAPE_TABLES,DegustationEtapes::ETAPE_ANONYMATS,DegustationEtapes::ETAPE_COMMISSION)) ){
           $lot->setIsPreleve();
        }

        if ($values['numero_table'] && in_array($degustation->etape, array(DegustationEtapes::ETAPE_TABLES,DegustationEtapes::ETAPE_ANONYMATS,DegustationEtapes::ETAPE_COMMISSION)) ) {
          $lot->setNumeroTable($values['numero_table']);
        }

        if ($degustation->isAnonymized()) {
            $key = $degustation->getNbLotsPreleves() + 1;
            $lot->anonymize($key);
        }

        $degustation->save();
        return $degustation;
    }
}
