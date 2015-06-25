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
        ));

        $this->widgetSchema->setLabels(array(
                'generation'  => 'Export',
        ));

        $this->setValidators(array(
                'generation' => new sfValidatorChoice(array('choices' => array_keys($choices), 'multiple' => false, 'required' => true)),
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

        return $this->generations[$this->values['generation']];
    }
}