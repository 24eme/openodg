<?php

class ParcellaireIrrigableValidation extends DocumentValidation {

    const TYPE_ERROR = 'erreur';
    const TYPE_WARNING = 'vigilance';
    const TYPE_ENGAGEMENT = 'engagement';

    public function __construct($document, $options = null) {
        parent::__construct($document, $options);
    }

    public function configure() {
        /*
         * Warning
         */
        $this->addControle(self::TYPE_WARNING, 'parcellaireirrigable_no_parcelles', 'Vous ne déclarez aucune parcelle irrigable');

        /*
         * Error
         */
    	$this->addControle(self::TYPE_ERROR, 'parcellaireirrigable_materiel_ressource_required', "Vous devez renseigner le matériel et la ressource de toutes vos parcelles");
    }

    public function controle() {
        if (count($this->document->declaration) < 1) {
        	$this->addPoint(self::TYPE_WARNING, 
        					'parcellaireirrigable_no_parcelles', 
        					'<a href="' . $this->generateUrl('parcellaireirrigable_parcelles', array('id' => $this->document->_id)) . "\" class='alert-link' >Séléctionner vos parcelles irrigables.</a>", 
        					'');
        }
        $missed = false;
        foreach ($this->document->declaration->getParcellesByCommune() as $commune => $parcelles) {
        	foreach ($parcelles as $parcelle) {
        		if (!$parcelle->materiel || !$parcelle->ressource) {
        			$missed = true;
        		}
        	}
        }
        if ($missed) {
        	$this->addPoint(self::TYPE_ERROR, 
        					'parcellaireirrigable_materiel_ressource_required', 
        					'<a href="' . $this->generateUrl('parcellaireirrigable_irrigations', array('id' => $this->document->_id)) . "\" class='alert-link' >Cliquer ici pour modifier la déclaration.</a>", 
        					'');
        }
    }
}
