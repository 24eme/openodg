<?php

class TourneeSaisieForm extends acCouchdbForm {

    public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
        $defaults = array();

        foreach($doc->getDegustationsObject() as $identifiant => $degustation) {
            foreach($degustation->prelevements as $prelevement) {
                $defaults["prelevement_".$identifiant."_".$prelevement->getKey()] = array(
                    "numero" => $prelevement->anonymat_degustation,
                    "etablissement" => "COMPTE-E".$degustation->identifiant,
                    "produit" => $prelevement->hash_produit,
                    "commission" => $prelevement->commission,
                );
            }
        }

        $defaults["prelevement_".uniqid()] = array("numero" => null, "etablissement" => null, "produit" => null, "commission" => 1);

        parent::__construct($doc, $defaults, $options, $CSRFSecret);
    }

    public function configure() {

        foreach($this->defaults as $key => $value) {
            if(!preg_match("/^prelevement_/", $key)) {
                continue;
            }
            $this->embedForm($key, new TourneeSaisieDegustationForm($this->getDocument(), $value));
        }
        $this->widgetSchema->setNameFormat('tournee_saisie[%s]');
    }

    public function getFormTemplate() {
        $form = new TourneeSaisieForm($this->getDocument());

        $form->embedForm('prelevement_var---nbItem---', new TourneeSaisieDegustationForm($this->getDocument()));

        $form->getWidgetSchema()->setNameFormat(sprintf("tournee_saisie[%%s]"));

        return $form['prelevement_var---nbItem---'];
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

            $this->embedForm($key, new TourneeSaisieDegustationForm($this->getDocument()));

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

        $degustations = array();
        foreach($values as $key => $value) {
            if(!preg_match("/^prelevement_/", $key)) {
                continue;
            }

            $identifiant = preg_replace("/^COMPTE-E/", "", $value['etablissement']);

            if(!array_key_exists($identifiant, $degustations)) {
                $degustation = DegustationClient::getInstance()->findOrCreateForSaisieByTournee($this->getDocument(), $identifiant);
                $degustations[$identifiant] = $degustation;
                $degustation->remove("prelevements");
                $degustation->add("prelevements");
            } else {
                $degustation = $degustations[$identifiant];
            }

            $prelevement = $degustation->prelevements->add();
            $prelevement->preleve = 1;
            $prelevement->cuve = null;
            $prelevement->commission = $value["commission"];
            $prelevement->hash_produit = $value["produit"];
            $prelevement->libelle = ConfigurationClient::getConfiguration()->get($prelevement->hash_produit)->getLibelleComplet();
            $prelevement->anonymat_degustation = $value["numero"];
        }

        $this->getDocument()->remove('degustations');
        $this->getDocument()->add('degustations');
        $this->getDocument()->resetDegustationsObject();

        foreach($degustations as $identifiant => $degustation) {
            $this->getDocument()->addDegustationObject($degustation);
        }

        $this->getDocument()->generateNotes();
        $this->getDocument()->updateNombrePrelevements();
        $this->getDocument()->updateNombreCommissionsFromDegustations();
        $this->getDocument()->save();
        $this->getDocument()->saveDegustations();
    }
}
