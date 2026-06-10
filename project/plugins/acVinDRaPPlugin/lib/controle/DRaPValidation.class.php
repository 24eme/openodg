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
        $this->addControle(self::TYPE_ERROR, 'drap_no_destination', 'Cette parcelle n\'a pas de destination');

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

        foreach ($this->document->declaration->getParcelles() as $parcelle) {
            if ($parcelle->getDestination() === null) {
                $this->addPoint(
                    self::TYPE_ERROR,
                    'drap_no_destination',
                    sprintf('La parcelle %s n\'a pas de destination renseignée', $parcelle->getParcelleId()),
                    $this->generateUrl('drap_destinations', ['id' => $this->document->_id])
                );
            }
        }
    }
}
