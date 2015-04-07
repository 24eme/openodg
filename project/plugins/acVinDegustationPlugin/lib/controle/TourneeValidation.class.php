<?php

class TourneeValidation extends DocumentValidation {

    const TYPE_WARNING = 'vigilance';

    public function __construct($document, $options = null) {
        parent::__construct($document, $options);
    }

    public function configure() {
        /*
         * Warning
         */
        $this->addControle(self::TYPE_WARNING, 'degustateur_no_email', "Cet dégustateur ne possède pas d'email");
        $this->addControle(self::TYPE_WARNING, 'operateur_no_email', "Cet opérateur ne possède pas d'email");

    }

    public function controle() {
        foreach ($this->document->operateurs as $operateur) {
            if (!$operateur->email) {
                $this->addPoint(self::TYPE_WARNING, 'operateur_no_email', sprintf("%s (%s, %s)", $operateur->raison_sociale, $operateur->cvi, $operateur->commune));
            }
        }

        foreach ($this->document->degustateurs as $degustateur_type => $degustateurs) {
            foreach ($degustateurs as $compte_id => $degustateur) {
                if (!$degustateur->email) {
                    $this->addPoint(self::TYPE_WARNING, 'degustateur_no_email', sprintf("%s (%s)", $degustateur->nom, $degustateur->commune));
                }
            }
        }


    }

}
