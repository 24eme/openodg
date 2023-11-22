<?php

class FactureMouvementEtablissementEditionLigneForm extends acCouchdbObjectForm {

    protected $isreadonly = array();

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        if ($object && $object->facture) {
            $this->isreadonly = array('readonly' => 'readonly', 'disabled' => 'disabled');
        }
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure() {
        $this->setWidget('identifiant', new WidgetEtablissement(array('interpro_id' => 'INTERPRO-declaration'), $this->isreadonly));
        $this->setWidget("identifiant_analytique", new sfWidgetFormChoice(array('choices' => $this->getIdentifiantsAnalytiques()), $this->isreadonly));
        $this->setWidget("type_libelle", new bsWidgetFormInput(array(), $this->isreadonly));
        $this->setWidget("detail_libelle", new bsWidgetFormInput(array(), array_merge(
            $this->isreadonly, ['list' => 'suggestions']
        )));
        $this->setWidget('suggestions', new sfWidgetDatalist(['choices' => $this->getSuggestionsFacturationLibre()]));
        $this->setWidget("quantite", new bsWidgetFormInputFloat(array(), $this->isreadonly));
        $this->setWidget("prix_unitaire", new bsWidgetFormInputFloat(array(), $this->isreadonly));

        $this->setValidator('identifiant', new ValidatorEtablissement(array('required' => false)));
        $this->setValidator("identifiant_analytique", new sfValidatorChoice(array('choices' => array_keys($this->getIdentifiantsAnalytiques()))));
        $this->setValidator("type_libelle", new sfValidatorString(array('required' => false)));
        $this->setValidator("detail_libelle", new sfValidatorString(array('required' => false)));
        $this->setValidator("quantite", new sfValidatorNumber(array('required' => false)));
        $this->setValidator("prix_unitaire", new sfValidatorNumber(array('required' => false)));

        $this->widgetSchema->setNameFormat('facture_mouvement_etablissement_edition_ligne[%s]');
    }

    protected function updateDefaultsFromObject() {
      parent::updateDefaultsFromObject();
      $lastMouvement = $this->getObject()->getDocument()->getLastMouvement();
      if ($this->getObject()->getKey() == 'nouveau') {
          $this->setDefault('type_libelle', $this->getObject()->getDocument()->libelle);
          if ($lastMouvement) {
              $this->setDefault('identifiant', $lastMouvement->identifiant);
              $this->setDefault('identifiant_analytique', $lastMouvement->identifiant_analytique);
              $this->setDefault('detail_libelle', $lastMouvement->detail_libelle);
              $this->setDefault('type_libelle', $lastMouvement->type_libelle);
              $this->setDefault('prix_unitaire', $lastMouvement->prix_unitaire);
          }
      }
    }

    public function getSociete() {
        return $this->getValidator('identifiant')->getDocument();
    }

    public function getIdentifiantsAnalytiques() {
        return ComptabiliteClient::getInstance()->findCompta()->getAllIdentifiantsAnalytiquesArrayForCompta();
    }

    public function getSuggestionsFacturationLibre() {
        return FactureConfiguration::getInstance()->getSuggestionsFacturationLibre();
    }
}
