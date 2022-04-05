<?php

class DegustationAjoutDegustateurForm extends acCouchdbForm {

    protected $degustateurs;
    protected $colleges;
    protected $table;

    public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
        $this->table = $options['table'];
        parent::__construct($doc, $defaults, $options, $CSRFSecret);
    }

	public function configure(){
    $this->colleges = DegustationConfiguration::getInstance()->getColleges();

    $this->setWidget('nom', new bsWidgetFormChoice(array('choices' => $this->getDegustateursByCollege())));
    $this->setValidator('nom', new sfValidatorChoice(array('choices' => array_keys($this->getDegustateursByCollege()))));

    $this->setWidget('college', new bsWidgetFormChoice(array('choices' => array_merge(array(""=>""), $this->colleges))));
    $this->setValidator('college', new sfValidatorChoice(array('choices' => array_keys($this->colleges))));

    $this->widgetSchema->setNameFormat('lot_form[%s]');
  }

  public function save() {
    $values = $this->getValues();
    $doc = $this->getDocument();
    $college = $values['college'];
    $compteId = $values['nom'];
    $table = ($this->table)? $this->table : null;
    $doc->addDegustateur($compteId, $college, $table);
    $doc->save();
  }

  public function getDegustateursByCollege() {
    if (!$this->degustateurs) {
        $this->degustateurs = ["" => ""];
        foreach (DegustationConfiguration::getInstance()->getColleges() as $key => $college) {
            $this->degustateurs = array_merge($this->degustateurs, $this->getDocument()->listeDegustateurs($key, true));
        }
    }

    return $this->degustateurs;
  }

}
