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
        // $this->addControle(self::TYPE_WARNING, 'parcellaire_complantation', 'Attention');
        // $this->addControle(self::TYPE_ERROR, 'surface_vide', 'Superficie nulle (0 are)');

        /*
         * Error
         */
//        $this->addControle(self::TYPE_ERROR, 'parcellaire_invalidproduct', "Ce cépage non autorisé");
    }

    public function controle() {
        
    }
}
