<?php

class TravauxMarcFournisseursForm extends acCouchdbObjectForm {
    public function configure() {
        foreach($this->getObject() as $fournisseur) {
            $this->embedForm($fournisseur->getKey(), new TravauxMarcFournisseurForm($fournisseur));
        }

        $this->widgetSchema->setNameFormat("travauxmarc_fournisseurs[%%s]");
    }

    public function bind(array $taintedValues = null, array $taintedFiles = null) {
        foreach ($this->embeddedForms as $key => $form) {
            if (array_key_exists($key, $taintedValues)) {
                continue;
            }
            $this->unEmbedForm($key);
        }

        foreach ($taintedValues as $key => $values) {
            if (!is_array($values) || array_key_exists($key, $this)) {
                continue;
            }
            $this->embedForm($key, new TravauxMarcFournisseurForm($this->getObject()->add()));
        }

        return parent::bind($taintedValues, $taintedFiles);
    }

    protected function doUpdateObject($values) {
        $valuesToUpdate = $values;
        unset($valuesToUpdate['_revision']);

        foreach($valuesToUpdate as $key => $value) {
            if($value['etablissement_id'] || $value['date_livraison'] || $value['quantite']) {
                continue;
            }

            unset($valuesToUpdate[$key]);
        }

        $this->getObject()->remove('fournisseurs');
        $this->getObject()->clear();
        $this->getObject()->fromArray($valuesToUpdate);
    }

    public function unEmbedForm($key) {
        unset($this->widgetSchema[$key]);
        unset($this->validatorSchema[$key]);
        unset($this->embeddedForms[$key]);
    }

}
