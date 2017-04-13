<?php

class TourneeSaisieDegustateursForm extends acCouchdbForm {

    const NOEUD_TYPE_DEGUSTATEUR = "DEFAUT";

    public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
        $defaults = array();

        foreach($doc->degustateurs->add(self::NOEUD_TYPE_DEGUSTATEUR) as $idCompte => $degustateur) {
            $defaults["degustateur_".$idCompte] = array(
                "compte" => $idCompte.",".$degustateur->nom.' ('.str_replace("COMPTE-D", "", $idCompte).') à '.$degustateur->commune.' ('.$degustateur->code_postal.')',
            );
        }

        $defaults["degustateur_".uniqid()] = array("compte" => null);

        parent::__construct($doc, $defaults, $options, $CSRFSecret);
    }

    public function configure() {

        foreach($this->defaults as $key => $value) {
            if(!preg_match("/^degustateur_/", $key)) {
                continue;
            }
            $this->embedForm($key, new TourneeSaisieDegustateurForm($this->getDocument(), $value));
        }

        $this->widgetSchema->setNameFormat('tournee_saisie_degustateurs[%s]');
    }

    public function getFormTemplate() {
        $form = new TourneeSaisieDegustateursForm($this->getDocument());

        $form->embedForm('degustateur_var---nbItem---', new TourneeSaisieDegustateurForm($this->getDocument()));

        $form->getWidgetSchema()->setNameFormat(sprintf("tournee_saisie[%%s]"));

        return $form['degustateur_var---nbItem---'];
    }

    public function bind(array $taintedValues = null, array $taintedFiles = null) {
        foreach ($this->embeddedForms as $key => $form) {
            if (!array_key_exists($key, $taintedValues)) {
                $this->unEmbedForm($key);
                unset($taintedValues[$key]);
            }
        }
        foreach ($taintedValues as $key => $values) {
            if (!is_array($values) || array_key_exists($key, $this->embeddedForms)) {
                continue;
            }

            $this->embedForm($key, new TourneeSaisieDegustateurForm($this->getDocument()));

        }
        $nodes_to_remove = array();

        foreach ($nodes_to_remove as $nodeToRemoveKey) {
            $keyEmbedded = explode('_', $nodeToRemoveKey);
            $this->unEmbedFormAndRemoveNode($keyEmbedded[0], $keyEmbedded[1], $taintedValues);
        }
        return parent::bind($taintedValues, $taintedFiles);
    }

    public function unEmbedForm($key) {
        unset($this->widgetSchema[$key]);
        unset($this->validatorSchema[$key]);
        unset($this->embeddedForms[$key]);
    }

    public function unEmbedFormAndRemoveNode($socId, $uniqkey, &$taintedValues) {
        $this->getObject()->getOrAdd($socId)->remove($uniqkey);
        if (!count($this->getObject()->getOrAdd($socId))) {
            $this->getObject()->remove($socId);
        }
        $key = $socId . '_' . $uniqkey;
        unset($this->widgetSchema[$key]);
        unset($this->validatorSchema[$key]);
        unset($this->embeddedForms[$key]);
        unset($taintedValues[$key]);
    }

    public function updateDoc() {
        $values = $this->getValues();

        $this->getDocument()->degustateurs->remove(self::NOEUD_TYPE_DEGUSTATEUR);
        $this->getDocument()->degustateurs->add(self::NOEUD_TYPE_DEGUSTATEUR);

        foreach($values as $key => $value) {
            if(!preg_match("/^degustateur_/", $key)) {
                continue;
            }

            $degustateur = $this->getDocument()->addDegustateur(self::NOEUD_TYPE_DEGUSTATEUR, $value["compte"]);
            $degustateur->presence = 1;
        }

        $this->getDocument()->save();
    }
}
