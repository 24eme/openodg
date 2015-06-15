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
        $this->addControle(self::TYPE_WARNING, 'operateur_non_affecte', "Cet opérateur n'a pas été affecté à une tournée");
        $this->addControle(self::TYPE_WARNING, 'conflit_interet', "Ces intervenants ont des rôles différents dans la tournée / dégustation");

    }

    public function controle() {
        $conflits = array();

        foreach ($this->document->operateurs as $operateur) {
            if (!$operateur->email) {
                $this->addPoint(self::TYPE_WARNING, 'operateur_no_email', sprintf("%s (%s, %s)", $operateur->raison_sociale, $operateur->cvi, $operateur->commune));
            }

            if(!$operateur->isAffecteTournee()) {
                $this->addPoint(self::TYPE_WARNING, 'operateur_non_affecte', sprintf("%s (%s, %s)", $operateur->raison_sociale, $operateur->cvi, $operateur->commune));
            }

            if($operateur->email) {
                $conflits[$operateur->email][] = sprintf("L'opérateur : %s (%s, %s)", $operateur->raison_sociale, $operateur->cvi, $operateur->commune);
            }

            $conflits[KeyInflector::slugify($operateur->raison_sociale)][] = sprintf("L'opérateur : %s (%s, %s)", $operateur->raison_sociale, $operateur->cvi, $operateur->commune);
        }

        foreach ($this->document->degustateurs as $degustateur_type => $degustateurs) {
            foreach ($degustateurs as $compte_id => $degustateur) {
                if (!$degustateur->email) {
                    $this->addPoint(self::TYPE_WARNING, 'degustateur_no_email', sprintf("%s (%s)", $degustateur->nom, $degustateur->commune));
                }
                
                if($degustateur->email) {
                    $conflits[$degustateur->email][] = sprintf("Le dégustateur %s : %s (%s)", $degustateur_type, $degustateur->nom, $degustateur->commune);
                }

                $conflits[KeyInflector::slugify($degustateur->nom)][] = sprintf("Le dégustateur %s : %s (%s)", $degustateur_type, $degustateur->nom, $degustateur->commune);
            }
        }

        foreach($conflits as $interlocuteurs) {
            if(count($interlocuteurs) < 2) {
                continue;
            }

            $this->addPoint(self::TYPE_WARNING, 'conflit_interet', implode(" / ", $interlocuteurs));
        }
    }

}
