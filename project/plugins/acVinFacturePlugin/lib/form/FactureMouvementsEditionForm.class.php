<?php

/**
 * Description of FactureMouvementsEditionForm
 *
 * @author mathurin
 */
class FactureMouvementsEditionForm extends acCouchdbObjectForm {

    public function configure() {
        $this->setWidget("libelle", new sfWidgetFormInput());
        $this->setValidator("libelle", new sfValidatorString(array("required" => true)));
        $this->widgetSchema->setLabel('libelle', 'Libellé opération');

        $this->embedForm('mouvements', new FactureMouvementEditionLignesForm($this->getObject()));

        $this->widgetSchema->setNameFormat('facture_mouvements_edition[%s]');
    }

    protected function doUpdateObject($values) {
      $mouvements = $values['mouvements'];
      unset($values['mouvements']);
    	parent::doUpdateObject($values);
      if (!$this->getObject()->date) {
            $this->getObject()->set('date', date('Y-m-d'));
            $this->getObject()->getOrAdd('valide')->set('date_saisie', date('Y-m-d'));
      }
      $inserted_keys = array();
      $ordre = $this->getObject()->getStartIndexForSaisieForm();
      foreach($mouvements as $cle => $mouvement) {
          $kExploded = explode('_', $cle);
          $id = $kExploded[1];
          $key = $kExploded[2];
          if (!$mouvement['identifiant']||!$mouvement['quantite']||!$mouvement['prix_unitaire']) {
            continue;
          }
          if ($id == 'nouveau') {
            $k = uniqid();
            $societe = EtablissementClient::getInstance()->find($mouvement['identifiant']);
            $societeMvtKey = $societe->identifiant;
          } else {
            $k = $key;
            $societe = null;
            $societeMvtKey = $id;
          }

          $mvt = $this->getObject()->mouvements->getOrAdd($societeMvtKey)->getOrAdd($k);
          $inserted_keys[$societeMvtKey.'_'.$k] = 1;

          $mvt->identifiant = $societeMvtKey;
          $mvt->updateIdentifiantAnalytique($mouvement['identifiant_analytique']);
          $mvt->type_libelle = $mouvement['type_libelle'];
          $mvt->detail_libelle = $mouvement['detail_libelle'];
          $mvt->quantite = floatval($mouvement['quantite']);
          $mvt->prix_unitaire = floatval($mouvement['prix_unitaire']);
          $mvt->taux = $mvt->prix_unitaire;
          if (!$mvt->facture) {
            $mvt->facture = 0;
          }
          $mvt->facturable = 1;
          $mvt->date = $this->getObject()->date;
          $mvt->id = $this->getObject()->_id;
          $mvt->type = $this->getObject()->type;
          $mvt->region = $this->getObject()->region;
      }
      // Suppression des lignes supprimees dynamiquement
      $mvtsToRemove = array();
      foreach ($this->getObject()->getOrAdd('mouvements') as $etbId => $mvtsEtb) {
          foreach ($mvtsEtb as $keyMvt => $mvt) {
              if (!$mvt->facture && !isset($inserted_keys[$etbId . '_' . $keyMvt])) {
                  $mvtsToRemove[] = $mvt;
              }
          }
      }
      foreach ($mvtsToRemove as $mvtToRemove) {
          $mvtToRemove->delete();
      }
      if ($this->getObject()->mouvements->exist('nouveau')) {
        $this->getObject()->mouvements->remove('nouveau');
      }
    }

    public function bind(array $taintedValues = null, array $taintedFiles = null) {
        foreach ($this->embeddedForms as $key => $form) {
            if($taintedValues && $form instanceof FactureMouvementEditionLignesForm) {
                $files = ($taintedFiles && isset($taintedFiles[$key]))? $taintedFiles[$key] : null;
                $form->bind($taintedValues[$key], $files);
                $this->updateEmbedForm($key, $form);
            }
        }
        parent::bind($taintedValues, $taintedFiles);
    }

    public function updateEmbedForm($name, $form) {
        $this->widgetSchema[$name] = $form->getWidgetSchema();
        $this->validatorSchema[$name] = $form->getValidatorSchema();
    }
}
