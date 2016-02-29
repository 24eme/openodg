<?php

class TirageValidation extends DocumentValidation {

    const TYPE_ERROR = 'erreur';
    const TYPE_WARNING = 'vigilance';
    const TYPE_ENGAGEMENT = 'engagement';

    public function __construct($document, $options = null) {
        parent::__construct($document, $options);
    }

    public function configure() {
        $this->addControle(self::TYPE_ERROR, 'composition_incomplete', "Vous n'avez pas saisie d'information relative à la composition de votre lot");
        $this->addControle(self::TYPE_ERROR, 'couleur_cepage', "Pour le rosé, il n'est pas possible d'avoir un autre cépage que le Pinot noir");
        $this->addControle(self::TYPE_ERROR, 'assemblage_no_ventilation', "Pour les millésimes assemblés, il est nécessaire d'indiquer la ventilation");
        $this->addControle(self::TYPE_ERROR, 'date_mise_en_bouteille_debut', "La date de début de mise en bouteille ne peut pas être inférieure au 1er décembre");
        $this->addControle(self::TYPE_ERROR, 'date_mise_en_bouteille_fin', "La date de fin de mise en bouteille ne peut pas être inférieure à la date de début");
      	$this->addControle(self::TYPE_ENGAGEMENT, TirageDocuments::DOC_PRODUCTEUR, "Joindre une copie de votre Déclaration de Récolte");
        $this->addControle(self::TYPE_ENGAGEMENT, TirageDocuments::DOC_ACHETEUR, "Joindre une copie de votre Certificat de Fabrication visé par les douanes ou une copie de la DRM visé par les Douanes");
        $this->addControle(self::TYPE_WARNING, 'famille_elaborateur', "Vous n'êtes pas élaborateur");
    }

    public function controle() {
        
        $isRoseCepageNonPN = false;
        if($this->document->couleur == TirageClient::COULEUR_ROSE){
            foreach ($this->document->getCepagesSelectionnes() as $cepage){
                if($cepage->libelle != 'Pinot noir'){
                    $isRoseCepageNonPN = true;
                    break;
                }
            }
        }
        if($isRoseCepageNonPN){
            $this->addPoint(self::TYPE_ERROR, 'couleur_cepage','', $this->generateUrl('tirage_vin',  $this->document));
        }
        if(($this->document->millesime == TirageClient::MILLESIME_ASSEMBLE) && !$this->document->millesime_ventilation){
            $this->addPoint(self::TYPE_ERROR, 'assemblage_no_ventilation','', $this->generateUrl('tirage_vin',  $this->document));
        }
        if ($this->document->isNegociant()) {
            $this->addPoint(self::TYPE_ENGAGEMENT, TirageDocuments::DOC_ACHETEUR, null);
        } else {
            $this->addPoint(self::TYPE_ENGAGEMENT, TirageDocuments::DOC_PRODUCTEUR, null);
        }
        $composition_incomplete = true;
        if (count($this->document->composition)) {
            foreach ($this->document->composition as $compo) {
                if ($compo->nombre) {
                    $composition_incomplete = false;
                    break;
                }
            }
        }
        if($composition_incomplete){
            $this->addPoint(self::TYPE_ERROR, 'composition_incomplete','', $this->generateUrl('tirage_lots',  $this->document));
        }
        
        if (!$this->document->getEtablissementObject()->hasFamille(EtablissementClient::FAMILLE_ELABORATEUR)) {
        	$this->addPoint(self::TYPE_WARNING, 'famille_elaborateur', null);
        }
        if ($this->document->date_mise_en_bouteille_debut < $this->document->campagne.'-12-01') {
            $this->addPoint(self::TYPE_ERROR, 'date_mise_en_bouteille_debut','', $this->generateUrl('tirage_lots',  $this->document));
        }
        if ($this->document->date_mise_en_bouteille_fin < $this->document->date_mise_en_bouteille_debut) {
            $this->addPoint(self::TYPE_ERROR, 'date_mise_en_bouteille_fin','', $this->generateUrl('tirage_lots',  $this->document));
        }
    }

}
