<?php

class FacturationForm extends BaseForm {
	
	protected $templatesFactures;
	
	public function __construct($templatesFactures, $defaults = array(), $options = array(), $CSRFSecret = null)
  	{
  		$this->templatesFactures = $templatesFactures;
    	parent::__construct($defaults, $options, $CSRFSecret);
  	}
	
    public function configure() {
    	$choices = $this->getChoices();
        $this->setWidgets(array(
                'declarant'   => new sfWidgetFormInput(),
                'template_facture'   => new sfWidgetFormChoice(array('choices' => $choices)),
        ));

        $this->widgetSchema->setLabels(array(
                'declarant'  => 'Déclarant : ',
                'template_facture'  => 'Template de facture : ',
        ));

        $this->setValidators(array(
                'declarant' => new sfValidatorString(array("required" => true)),
                'template_facture' => new sfValidatorChoice(array('choices' => array_keys($choices), 'multiple' => false, 'required' => true)),
        ));
        $this->widgetSchema->setNameFormat('facturation[%s]');
    }
    
    public function getChoices()
    {
    	$choices = array();
    	foreach ($this->templatesFactures as $templateFacture) {
    		$choices[$templateFacture->_id] = $templateFacture->libelle;
    	}
    	return $choices;
    }

}

