<?php

class TirageValidation extends DocumentValidation
{
    const TYPE_ERROR = 'erreur';
    const TYPE_WARNING = 'vigilance';
    const TYPE_ENGAGEMENT = 'engagement';
    
    public function __construct($document, $options = null)
    {
        parent::__construct($document, $options);
        
    }
    
    public function configure() 
    {
        $this->addControle(self::TYPE_ENGAGEMENT, TirageDocuments::DOC_PRODUCTEUR, "Joindre une copie de votre Déclaration de Récolte");
        $this->addControle(self::TYPE_ENGAGEMENT, TirageDocuments::DOC_ACHETEUR, "Joindre une copie de votre Certificat de Fabrication visé par les douanes ou une copie de la DRM visé par les Douanes");
    }

    public function controle()
    {
        $this->addPoint(self::TYPE_ENGAGEMENT, TirageDocuments::DOC_PRODUCTEUR, null);
        $this->addPoint(self::TYPE_ENGAGEMENT, TirageDocuments::DOC_ACHETEUR, null);
    }
    
}