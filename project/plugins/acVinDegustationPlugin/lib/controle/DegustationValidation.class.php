<?php

class DegustationValidation extends DocumentValidation {

    const TYPE_WARNING = 'vigilance';

    public function __construct($document, $options = null) {
        parent::__construct($document, $options);
    }

    public function configure() {
        /*
         * Warning
         */
        $this->addControle(self::TYPE_WARNING, 'degustateur_no_email', 'Attention');

    }

    public function controle() {        
        foreach ($this->document->degustateurs as $degustateur_type => $degustateurs) {
            foreach ($degustateurs as $compte_id => $infos_compte) {
                if (!$infos_compte->email) {
                    $this->addPoint(self::TYPE_WARNING, 'degustateur_no_email', 'le dégustateur ' . $infos_compte->nom . ' ne possède pas d\'email');
                }
            }
        }
    }

}
