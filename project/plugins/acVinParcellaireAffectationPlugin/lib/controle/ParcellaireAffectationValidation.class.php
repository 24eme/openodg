<?php

class ParcellaireAffectationValidation extends DocumentValidation {

    const TYPE_ERROR = 'erreur';
    const TYPE_WARNING = 'vigilance';
    const TYPE_ENGAGEMENT = 'engagement';

    public function __construct($document, $options = null) {
        parent::__construct($document, $options);
    }

    public function configure() {
        $this->addControle(self::TYPE_WARNING, 'superficie_douane_depassee', "La superficie affectée est supérieure à celle de votre déclaration douanière");
    }

    public function controle() {
        foreach ($this->document->getParcelles() as $parcelle) {
            if (! $parcelle->affectee) {
                continue;
            }

            if ($parcelle->getSuperficieParcellaire() < $parcelle->superficie) {
                $this->addPoint(self::TYPE_WARNING, 'superficie_douane_depassee', "La parcelle <strong>$parcelle->section / $parcelle->numero_parcelle</strong> ($parcelle->superficie ha) dépasse celle de votre parcellaire (".$parcelle->getSuperficieParcellaire()." ha)");
            }
        }
    }
}
