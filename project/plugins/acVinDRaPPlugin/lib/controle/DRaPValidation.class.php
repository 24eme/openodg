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
         if(ParcellaireConfiguration::getInstance()->hasIrrigableMateriel()){
             $this->addControle(self::TYPE_ERROR, 'parcellaireirrigable_materiel_required', "Vous devez renseigner le matériel de toutes vos parcelles");
         }

         if(ParcellaireConfiguration::getInstance()->hasIrrigableRessource()){
             $this->addControle(self::TYPE_ERROR, 'parcellaireirrigable_ressource_required', "Vous devez renseigner la ressource de toutes vos parcelles");
         }

        /*
         * Engagements
         */
         $this->addControle(self::TYPE_ENGAGEMENT, ParcellaireIrrigableDocuments::ENGAGEMENT_A_NE_PAS_IRRIGUER, "Je m'engage à ne pas irriguer les parcelles ayant fait l'objet d'une déclaration préalable d’affectation parcellaire en vue de la revendication potentielle d'AOC Alsace Communale, AOC Alsace Lieu-dit, AOC Alsace Grand Cru et mentions VT/SGN.");
         $this->addControle(self::TYPE_ENGAGEMENT, 'parcellaireirrigable_si_irrigue_pas_de_vci', "Déclarer une parcelle comme irrigable ne vous engage pas à l'irriguer. Si vous procédez à une irrigation, vous devrez le déclarer — et cette parcelle ne pourra alors pas produire de VCI pour la campagne en cours.");
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
        		if (!$parcelle->materiel) {
        			$missedMateriel = true;
        		}
                if (!$parcelle->ressource) {
                    $missedRessource = true;
                }
        	}
        }
        if ($missedMateriel && ParcellaireConfiguration::getInstance()->hasIrrigableMateriel()) {
        	$this->addPoint(self::TYPE_ERROR,
        					'parcellaireirrigable_materiel_required',
        					'<a href="' . $this->generateUrl('parcellaireirrigable_irrigations', array('id' => $this->document->_id)) . "\" class='alert-link' >Cliquer ici pour modifier la déclaration.</a>",
        					'');
        }

        if ($missedRessource && ParcellaireConfiguration::getInstance()->hasIrrigableRessource()) {
            $this->addPoint(self::TYPE_ERROR,
                            'parcellaireirrigable_ressource_required',
                            '<a href="' . $this->generateUrl('parcellaireirrigable_irrigations', array('id' => $this->document->_id)) . "\" class='alert-link' >Cliquer ici pour modifier la déclaration.</a>",
                            '');
        }

        if (ParcellaireConfiguration::getInstance()->hasEngagementANePasIrriguer()) {
            $this->addPoint(self::TYPE_ENGAGEMENT, ParcellaireIrrigableDocuments::ENGAGEMENT_A_NE_PAS_IRRIGUER, null);
        }

        if (ParcellaireConfiguration::getInstance()->hasEngagementVciIrrigation()) {
            $this->addPoint(self::TYPE_ENGAGEMENT, 'parcellaireirrigable_si_irrigue_pas_de_vci',
            '<input type="checkbox" class=alert-link>Déclarer une parcelle comme irrigable ne vous engage pas à l\'irriguer. Si vous procédez à une irrigation, vous devrez le déclarer — et cette parcelle ne pourra alors pas produire de VCI pour la campagne en cours.</input>');
        }
    }
}
