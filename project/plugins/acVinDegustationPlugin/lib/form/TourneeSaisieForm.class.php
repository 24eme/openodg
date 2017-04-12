<?php

class TourneeSaisieForm extends acCouchdbForm {

    public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
        $defaults = array();

        foreach($doc->getDegustationsObject() as $identifiant => $degustation) {
            foreach($degustation->prelevements as $prelevement) {
                $defaults["prelevement_".$identifiant."_".$prelevement->getKey()] = array(
                    "numero" => $prelevement->anonymat_degustation,
                    "etablissement" => "COMPTE-E".$degustation->identifiant.",".$degustation->raison_sociale.' ('.$degustation->cvi.') à '.$degustation->commune.' ('.$degustation->code_postal.')',
                    "produit" => $prelevement->hash_produit
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
        /*foreach ($taintedValues as $key => $values) {
            if (array_key_exists($key, $this->embeddedForms)) {
                foreach ($values as $keyValue => $value) {
                    if ($keyValue == 'identifiant') {
                        $keyEmbedded = explode('_', $key);
                        if (($keyEmbedded[0] != str_replace('SOCIETE-', '', $value) . '01') && $keyEmbedded[0] != "nouveau") {

                            if ($value && SocieteClient::getInstance()->find($value) && $values['quantite']) {
                                $identifiant = str_replace("SOCIETE-", "", $values['identifiant']) . '01';

                                $keyMvt = $keyEmbedded[1];
                                $newKey = $identifiant . '_' . $keyMvt;

                                $mouvementCloned = clone $this->getObject()->getOrAdd($keyEmbedded[0])->get($keyEmbedded[1]);
                                $mouvementCloned->identifiant = str_replace("SOCIETE-", "", $values['identifiant']) . '01';

                                $mouvement = $this->getObject()->getOrAdd($mouvementCloned->identifiant)->add($keyMvt, $mouvementCloned);

                                $this->embedForm($newKey, new FactureMouvementEtablissementEditionLigneForm($mouvement, array('interpro_id' => $this->interpro_id, 'keyMvt' => $newKey)));
                                $taintedValues[$newKey] = $taintedValues[$key];
                                $this->validatorSchema[$newKey] = $this->validatorSchema[$key];
                                $this->widgetSchema[$newKey] = $this->widgetSchema[$key];

                                $nodes_to_remove[] = $key;
                            }
                        }
                    }
                }
            }
        }*/

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
            $prelevement->cuve = "test";
            $prelevement->commission = 1;
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

        $this->getDocument()->updateNombrePrelevements();
        $this->getDocument()->save();
        $this->getDocument()->saveDegustations();
    }
}
