<?php

class ExportGenerationForm extends BaseForm
{
    protected $generations;

    public function __construct($generations, $defaults = array(), $options = array(), $CSRFSecret = null)
    {
        $this->generations = $generations;
        parent::__construct($defaults, $options, $CSRFSecret);
    }

    public function configure() {
        $choices = $this->getChoices();

        $this->setWidgets(array(
                'generation'   => new sfWidgetFormChoice(array('choices' => $choices)),
                'search'   => new bsWidgetFormTextarea(array(), array('style' => 'width: 100%;')),
        ));

        $this->widgetSchema->setLabels(array(
                'generation'  => 'Export',
                'search'  => 'Filtre',
        ));

        $this->setValidators(array(
                'generation' => new sfValidatorChoice(array('choices' => array_keys($choices), 'multiple' => false, 'required' => true)),
                'search' => new sfValidatorString(array('required' => false)),
        ));

        $this->widgetSchema->setNameFormat('generation_export[%s]');
    }

    public function getChoices()
    {
        $choices = array("" => "");

        foreach($this->generations as $key => $generation) {
            $choices[$key] = $generation->libelle;
        }

        return $choices;
    }

    public function getGeneration() {
      $generation = $this->generations[$this->values['generation']];
      if($search = trim($this->values['search'])){
        $identifiants = explode("\n", preg_replace("/^\n/", "",  preg_replace("/\n$/", "", preg_replace("/([^0-9\n]+|\n\n)/", "", str_replace("\n", "\n", $search)))));

      	foreach($identifiants as $index => $identifiant) {
      		$identifiants[$index] = trim($identifiant);
      		if(!$identifiants[$index]) {
      			unset($identifiants[$index]);
      		}
      	}
        $generation->getOrAdd('arguments')->add('search',implode(",", $identifiants));
      }
      return $this->generations[$this->values['generation']];
    }
}
