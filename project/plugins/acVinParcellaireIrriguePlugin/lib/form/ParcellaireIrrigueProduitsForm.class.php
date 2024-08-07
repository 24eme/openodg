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

		foreach ($this->getObject()->getDeclarationParcelles() as $pid => $parcelles) {
			$this->embedForm($pid, new ParcellaireIrrigueProduitIrrigationForm($parcelles));
		}

        $this->widgetSchema->setNameFormat('parcelles[%s]');
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        $defaults = $this->getDefaults();
        $defaults["date_papier"] = date('d/m/Y');
        $this->setDefaults($defaults);
        
    }

    protected function doUpdateObject($values) {
		parent::doUpdateObject($values);
    	if($this->getObject()->isPapier()) {
    		$this->getObject()->validate($values['date_papier']);
    	} else {
    		$this->getObject()->validate();
    	}
        $parcelles = $this->getObject()->getParcelles();
        foreach ($values as $pid => $items) {
            if (!isset($parcelles[$pid])) {
                continue;
            }
            $parcelle = $parcelles[$pid];
            $node = $this->getObject()->get($parcelle->getProduitHash());
            $node = $node->detail->get($pid);
            if ($items['irrigation'] && !$node->date_irrigation) {
                $node->add('irrigation', $items['irrigation']);
                $node->date_irrigation = date('Y-m-d');
            }
        }
    }

}
