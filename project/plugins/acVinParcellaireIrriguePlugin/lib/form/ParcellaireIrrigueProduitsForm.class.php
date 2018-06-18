<?php

class ParcellaireIrrigueProduitsForm extends acCouchdbObjectForm {

    public function configure() {
    	if($this->getObject()->isPapier()) {
    		$this->setWidget('date_papier', new sfWidgetFormInput());
    		$this->setValidator('date_papier', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));
    		$this->getWidget('date_papier')->setLabel("Date de réception du document");
    		$this->getValidator('date_papier')->setMessage("required", "La date de réception du document est requise");
    	}
    	
    	if (sfConfig::get('app_document_validation_signataire')) {
    		$this->setWidget('signataire', new sfWidgetFormInput());
    		$this->setValidator('signataire', new sfValidatorString(array('required' => true)));
    		$this->getWidget('signataire')->setLabel("Nom et prénom :");
    		$this->getValidator('signataire')->setMessage("required", "Le nom et prénom du signataire est requise");
    	}
    	
		foreach ($this->getObject()->declaration as $key => $value) {
			$this->embedForm($key, new ParcellaireIrrigueProduitIrrigationsForm($value));
		}

        $this->widgetSchema->setNameFormat('parcelles[%s]');
    }

    protected function doUpdateObject($values) {
		parent::doUpdateObject($values);
    	if($this->getObject()->isPapier()) {
    		$this->getObject()->validate($values['date_papier']);
    	} else {
    		$this->getObject()->validate();
    	}
    	foreach ($values as $produit => $value) {
    		if (!is_array($value)) continue;
    		foreach ($value as $detail => $items) {
    			$node = $this->getObject()->declaration->get($produit);
    			$node = $node->detail->get($detail);
    			foreach ($items as $k => $v) {
    				$node->add($k, $v);
    				if ($k == 'irrigation' && $v && !$node->date_irrigation) {
    					$node->date_irrigation = date('Y-m-d');
    				}
    			}
    		}
    	}
    }

}
