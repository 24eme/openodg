<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class CompteGroupeAjoutForm extends baseForm {


    protected $fonctionsArr = array();

  	public function __construct($interpro_id, $defaults = array(), $options = array(), $CSRFSecret = null)
  	{
  		$this->interpro_id = $interpro_id;
    	parent::__construct($defaults, $options, $CSRFSecret);
  	}

    public function configure()
    {

      $path_fonctions = dirname(__FILE__)."/../../../../data/configuration/rhone/fonctions.csv";
      $fonctionsCsv = new CsvFile($path_fonctions);

      foreach ($fonctionsCsv->getCsv() as $row) {
        $this->fonctionsArr[$row[1]] = $row[1];
      }

      $this->setWidget('id_etablissement', new WidgetEtablissement(array('interpro_id' => 'INTERPRO-declaration'), array('class' => 'select2autocomplete form-control')));
      $this->widgetSchema->setLabel('id_etablissement', 'Compte');
      $this->setValidator('id_etablissement', new ValidatorEtablissement(array('required' => true)));

      $this->setWidget('fonction', new bsWidgetFormChoice(array('choices' => $this->getFonctionList()), array("class"=>"select2 form-control")));
      $this->widgetSchema->setLabel('fonction', 'Fonction');
      $this->setValidator('fonction', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getFonctionList()))));

      $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
      $this->widgetSchema->setNameFormat('compte_groupe_ajout[%s]');
    }

    public function getFonctionList(){
      return $this->fonctionsArr;
    }

}
