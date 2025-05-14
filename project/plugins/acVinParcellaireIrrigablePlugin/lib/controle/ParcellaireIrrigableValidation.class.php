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
         if(! ParcellaireConfiguration::getInstance()->isSansIrrigableMaterielRessource()){
             $this->addControle(self::TYPE_ERROR, 'parcellaireirrigable_materiel_ressource_required', "Vous devez renseigner le matériel et la ressource de toutes vos parcelles");
         }


        /*
         * Engagements
         */
         $this->addControle(self::TYPE_ENGAGEMENT, ParcellaireIrrigableDocuments::ENGAGEMENT_A_NE_PAS_IRRIGUER, "Je m'engage à ne pas irriguer les parcelles ayant fait l'objet d'une déclaration préalable d’affectation parcellaire en vue de la revendication potententielle de dénominations géographiques complémentaires: lieux-dits, VT/SGN, Communales, Grands Crus.");
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
        if ($missed && (! ParcellaireConfiguration::getInstance()->isSansIrrigableMaterielRessource())) {
        	$this->addPoint(self::TYPE_ERROR, 
        					'parcellaireirrigable_materiel_ressource_required', 
        					'<a href="' . $this->generateUrl('parcellaireirrigable_irrigations', array('id' => $this->document->_id)) . "\" class='alert-link' >Cliquer ici pour modifier la déclaration.</a>", 
        					'');
        }

        $this->addPoint(self::TYPE_ENGAGEMENT, ParcellaireIrrigableDocuments::ENGAGEMENT_A_NE_PAS_IRRIGUER, null);
    }
}
