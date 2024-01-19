<?php
class AdelpheRepartitionForm extends acCouchdbObjectForm {

  public function configure() {
    $this->setWidgets(array(
      'conditionnement_bib' => new bsWidgetFormChoice(array('choices' => [1 => "Oui", 0 => "Non"], 'expanded' => true)),
      'repartition_bib' => new bsWidgetFormChoice(array('choices' => [1 => "Oui", 0 => "Non"], 'expanded' => true)),
      'volume_conditionne_bib' => new bsWidgetFormInputFloat(),
      'taux_conditionne_bib' => new bsWidgetFormInputFloat(),
    ));
    $this->setValidators(array(
      'conditionnement_bib' => new sfValidatorChoice(array('choices' => [0,1])),
      'repartition_bib' => new sfValidatorChoice(array('choices' => [0,1])),
      'volume_conditionne_bib' => new sfValidatorNumber(array('required' => false)),
      'taux_conditionne_bib' => new sfValidatorNumber(array('required' => false)),
    ));
    $this->widgetSchema->setNameFormat('adelphe[%s]');
  }

  protected function updateDefaultsFromObject() {
    parent::updateDefaultsFromObject();
    $defaults = $this->getDefaults();
    $defaults['taux_conditionne_bib'] = $this->getObject()->getTauxBibCalcule();
    $this->setDefaults($defaults);
  }

  protected function doUpdateObject($values) {
    if ($values['taux_conditionne_bib'] && !$values['volume_conditionne_bib']) {
      $values['volume_conditionne_bib'] = round($this->getObject()->volume_conditionne_total * $values['taux_conditionne_bib'] / 100);
    }
    parent::doUpdateObject($values);
    if (!$values['conditionnement_bib']) {
      $this->getObject()->conditionnementUniquementBouteille();
    } elseif (!$values['repartition_bib']) {
      $this->getObject()->conditionnementBibForfait();
    }
  }
}
