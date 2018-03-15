<?php
/**
 * Model for EtablissementLiaisonsOperateurs
 *
 */

class EtablissementLiaisonsOperateurs extends BaseEtablissementLiaisonsOperateurs {

    public function getChai(){
        if(!$this->hash_chai || !$this->id_etablissement) {

            return null;
        }

        $etablissement = $this->getEtablissementChai();

        if(!$etablissement || !$etablissement->exist($this->hash_chai)){

            return null;
        }

        return $etablissement->get($this->hash_chai);
    }

    public function getEtablissement() {

        return EtablissementClient::getInstance()->find($this->id_etablissement);
    }

    public function isSelfChai() {

        return !EtablissementClient::getInstance()->isChaiChezLautre($this->type_liaison);
    }

    public function getEtablissementChai() {
        if($this->isSelfChai()) {

            return $this->getDocument();
        };

        return $this->getEtablissement();
    }

    public function getTypeLiaisonLibelle() {
        $types_liaisons = EtablissementClient::getTypesLiaisons();

        return $types_liaisons[$this->type_liaison];
    }
}
