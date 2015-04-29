<?php

class FacturationForm extends BaseForm {

    /**
     * 
     */
	static public $choices = array(
		'AOC_ALSACE' => "AOC Alsace",
		'MARC_GEWURZTRAMINER' => "Marc d'Alsace Gewurztraminer",
		'REVUE' => "Abonnement revue",
	);
	
    public function configure() {
        $this->setWidgets(array(
                'declarant'   => new sfWidgetFormInput(),
                'type_facture'   => new sfWidgetFormChoice(array('choices' => self::$choices)),
        ));

        $this->widgetSchema->setLabels(array(
                'declarant'  => 'Déclarant : ',
                'type_facture'  => 'Type de facture : ',
        ));

        $this->setValidators(array(
                'declarant' => new sfValidatorString(array("required" => true)),
                'type_facture' => new sfValidatorChoice(array('choices' => array_keys(self::$choices), 'multiple' => false, 'required' => true)),
        ));
        $this->widgetSchema->setNameFormat('facturation[%s]');
    }

}

