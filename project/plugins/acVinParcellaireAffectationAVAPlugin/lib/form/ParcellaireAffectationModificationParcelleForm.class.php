<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ParcellaireAffectationAjoutParcelleForm
 *
 * @author mathurin
 */
class ParcellaireAffectationModificationParcelleForm extends ParcellaireAffectationParcelleForm {


    public function configure() {
        parent::configure();

        $this->widgetSchema->setNameFormat('parcellaire_modification_parcelle[%s]');
    }


    public function getAppellationNode() {

        return $this->getObject()->getAppellation();
    }


    public function getLieuDetailForAutocomplete() {
        $lieuxDetail = array();
        foreach ($this->getAppellationNode()->getLieuxEditable() as $libelle) {
            $lieuxDetail[] = $libelle;
        }
        $entries = array();
        foreach($lieuxDetail as $lieu) {
            $entry = new stdClass();
            $entry->id = trim($lieu);
            $entry->text = trim($lieu);
            $entries[] = $entry;
        }
        return $entries;
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();

        if(isset($this->widgetSchema['lieuCepage'])) {
            $this->setDefault('lieuCepage', $this->getObject()->getCepage()->getHashForKey());
        }

        if(isset($this->widgetSchema['cepage'])) {
            $this->setDefault('cepage', $this->getObject()->getCepage()->getHashForKey());
        }

        if(isset($this->widgetSchema['lieuDit'])) {
            $this->setDefault('lieuDit', $this->getObject()->lieu);
        }
    }

}
