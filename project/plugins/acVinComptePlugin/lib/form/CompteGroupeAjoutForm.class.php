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

      $this->setWidget('fonction', new bsWidgetFormInput());
      $this->widgetSchema->setLabel('fonction', 'Fonction');
      $this->setValidator('fonction', new sfValidatorString(array('required' => true)));

      $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
      $this->widgetSchema->setNameFormat('compte_groupe_ajout[%s]');
    }

    public function getFonctionList(){
      return $this->fonctionsArr;
    }

    public function getFonctionsForAutocomplete(){
      $q = new acElasticaQuery();
      $elasticaFacet   = new acElasticaFacetTerms('groupes');
      $elasticaFacet->setField('doc.groupes.fonction');
      $elasticaFacet->setSize(250);
      $q->addFacet($elasticaFacet);

      $index = acElasticaManager::getType('COMPTE');
      $resset = $index->search($q);
      $results = $resset->getResults();
      $this->facets = $resset->getFacets();

      ksort($this->facets);
      $entries = array();
      foreach ($this->facets["groupes"]["buckets"] as $facet) {
          if($facet["key"]){
            $entry = new stdClass();
            $entry->id = trim($facet["key"]);
            $entry->text = trim($facet["key"]);
            $entries[] = $entry;
        }
      }

      return $entries;
    }

}
