<?php
class DRevRevendicationProduitForm extends acCouchdbObjectForm 
{
   	public function configure()
    {
  		$this->setWidgets(array(
        	'total_superficie' => new sfWidgetFormInputFloat(),
  		    'volume_revendique' => new sfWidgetFormInputFloat()
    	));
        $this->widgetSchema->setLabels(array(
        	'total_superficie' => 'Superficie totale (ares):',
        	'volume_revendique' => 'Volume revendiqué (hl):',
        ));
        $this->setValidators(array(
        	'total_superficie' => new sfValidatorNumber(array('required' => false)),
        	'volume_revendique' => new sfValidatorNumber(array('required' => false))
        ));
  		$this->widgetSchema->setNameFormat('[%s]');
    }
    
    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
        $this->getObject()->defineActive();
        if (!$this->getObject()->actif) {
        	$this->getObject()->clear();
        }
    }
}