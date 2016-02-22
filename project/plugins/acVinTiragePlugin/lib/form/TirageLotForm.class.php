<?php

class TirageLotForm extends acCouchdbObjectForm
{
	public function configure()
	{
		$choices = $this->getContenances();
        $this->setWidgets(array(
            'nombre' => new bsWidgetFormInputInteger(),
			'contenance' => new bsWidgetFormChoice(array('choices' => $choices)),
        ));
        $this->setValidators(array(
            'nombre' => new sfValidatorInteger(array('required' => true)),
			'contenance' => new sfValidatorChoice(array('choices' => array_keys($choices), 'required' => true)),
        ));
        $this->widgetSchema->setNameFormat('[%s]');
    }
    
    public function getContenances()
    {
    	$contenances = array();
    	foreach (array_keys(sfConfig::get('app_contenances_bouteilles')) as $v) {
    		$contenances[$v] = $v;
    	}
    	return $contenances;
    }
}