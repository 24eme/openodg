<?php

class DRaPValidation extends DocumentValidation {

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
        $this->addControle(self::TYPE_WARNING, 'drap_no_parcelles', 'Vous ne déclarez aucune parcelle en renonciation à produire');

        /*
         * Error
         */

        /*
         * Engagements
         */
    }

    public function controle() {
        if (count($this->document->declaration) < 1) {
            $this->addPoint(self::TYPE_WARNING,
                'drap_no_parcelles',
                '<a href="' . $this->generateUrl('drap_parcelles', array('id' => $this->document->_id)) . "\" class='alert-link' >Séléctionner vos parcelles en renonciation à produire.</a>",
                    '');
        }
    }
}
