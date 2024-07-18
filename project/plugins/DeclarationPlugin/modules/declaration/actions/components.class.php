<?php

class declarationComponents extends sfComponents {

    public function executeParcellairesLies(sfWebRequest $request) {
        $this->parcellairesLies = [
            'PARCELLAIREAFFECTATION' => [
                'libelle' => 'Affectation parcellaire',
                'obj' => ParcellaireAffectationClient::getInstance()->find('PARCELLAIREAFFECTATION-' . $this->obj->identifiant . '-' . $this->obj->periode),
                'id' => ParcellaireAffectationClient::getInstance()->buildId($this->obj->identifiant, $this->obj->periode)],

            'PARCELLAIREMANQUANT' => [
                'libelle' => 'Pieds manquants',
                'obj' => ParcellaireManquantClient::getInstance()->find('PARCELLAIREMANQUANT-' . $this->obj->identifiant . '-' . $this->obj->periode),
                'id' => ParcellaireManquantClient::getInstance()->buildId($this->obj->identifiant, $this->obj->periode)],

            'PARCELLAIREIRRIGABLE' => [
                'libelle' => 'Irrigation',
                'obj' => ParcellaireIrrigableClient::getInstance()->find('PARCELLAIREIRRIGABLE-' . $this->obj->identifiant . '-' . $this->obj->periode),
                'id' => ParcellaireIrrigableClient::getInstance()->buildId($this->obj->identifiant, $this->obj->periode)],
        ];
    }
}
