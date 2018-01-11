<?php

class TourneeSaisieValidation extends DocumentValidation {

    const TYPE_WARNING = 'vigilance';
    const TYPE_ERROR = 'erreur';

    public function __construct($document, $options = null) {
        parent::__construct($document, $options);
    }

    public function configure() {
        /*
         * Warning
         */
        $this->addControle(self::TYPE_WARNING, 'degustateur_produit', "Ce dégustateur n'est pas déclaré pour ce produit");
        $this->addControle(self::TYPE_WARNING, 'prelevement_drev', "Cet opérateur n'a pas déclaré de lots dans sa drev pour le produit dégusté");

    }

    public function controle() {
        foreach ($this->document->degustateurs as $degustateur_type => $degustateurs) {
            foreach ($degustateurs as $compte_id => $degustateur) {
                $compte = CompteClient::getInstance()->find($compte_id);
                if(!$compte->exist('infos/produits/'.str_replace("/", "-", $this->document->produit))) {
                    $this->addPoint(self::TYPE_WARNING, 'degustateur_produit', sprintf("%s : %s", $degustateur->nom, $this->document->libelle));
                }
            }
        }

        foreach($this->document->getDegustationsObject() as $degustation) {
            $drev = DRevClient::getInstance()->find($degustation->drev);
            foreach($degustation->prelevements as $prelevement) {
                $finded = false;
                foreach($drev->prelevements as $prelevementDrev) {
                    foreach ($prelevementDrev->lots as $lot) {
                        if($prelevement->hash_produit == $lot->hash_produit) {
                            $finded = true;
                            break;
                        }
                    }

                    if($finded) {
                        break;
                    }
                }
                if(!$finded) {
                    $this->addPoint(self::TYPE_WARNING, 'prelevement_drev', sprintf("%s : %s", $degustation->raison_sociale, $prelevement->getLibelleComplet()));
                }
            }
        }
    }

}
