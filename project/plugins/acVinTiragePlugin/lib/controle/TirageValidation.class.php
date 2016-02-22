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
        $this->addControle(self::TYPE_ENGAGEMENT, TirageDocuments::DOC_PRODUCTEUR, "Joindre une copie de votre Déclaration de Récolte");
        $this->addControle(self::TYPE_ENGAGEMENT, TirageDocuments::DOC_ACHETEUR, "Joindre une copie de votre Certificat de Fabrication visé par les douanes ou une copie de la DRM visé par les Douanes");
    }

    public function controle() {
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
    }

}
