<?php

class DegustationAjoutDegustateurForm extends acCouchdbForm {

    protected $degustateurs;
    protected $colleges;
    protected $table;

    public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
        $this->table = $options['table'];
        parent::__construct($doc, $defaults, $options, $CSRFSecret);
    }

	public function configure(){
    $this->colleges = DegustationConfiguration::getInstance()->getColleges();

    $this->setWidget('nom', new bsWidgetFormChoice(array('choices' => $this->getDegustateursByCollege())));
    $this->setValidator('nom', new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->getDegustateursByCollege()))));

    $this->setWidget('college', new bsWidgetFormChoice(array('choices' => $this->colleges)));
    $this->setValidator('college', new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->colleges))));

    $this->widgetSchema->setNameFormat('lot_form[%s]');
  }

  public function save() {
    $values = $this->getValues();
    $doc = $this->getDocument();
    $college = $values['college'];
    $compteId = $values['nom'];
    $table = ($this->table)? $this->table : null;
    $doc->addDegustateur($compteId, $college, $table);
    $doc->save();
  }



  public function getDegustateursByCollege() {
    if (!$this->degustateurs) {
        $this->degustateurs = array();
        foreach (DegustationConfiguration::getInstance()->getColleges() as $key => $college) {
          $comptes = CompteTagsView::getInstance()->listByTags('automatique', $key);

          if (count($comptes) > 0) {
              $result = array();
              foreach ($comptes as $compte) {
                  $degustateur = CompteClient::getInstance()->find($compte->id);
                  $libelle = (isset($compte->key[CompteTagsView::KEY_LIBELLEWITHADRESSE_COMPTE]))? $compte->key[CompteTagsView::KEY_LIBELLEWITHADRESSE_COMPTE] : null;
                  if (!$libelle) {
                    $degustateur = CompteClient::getInstance()->find($compte->id);
                    $libelle = $degustateur->getLibelleWithAdresse();
                  }
                  $this->degustateurs[$compte->id] = $libelle;
              }
          }
        }

        uasort($this->degustateurs, function ($deg1, $deg2) {
            return strcasecmp($deg1, $deg2);
        });
    }
    return $this->degustateurs;
  }

}
