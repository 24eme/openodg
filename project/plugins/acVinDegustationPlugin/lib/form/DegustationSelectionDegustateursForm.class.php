<?php

class DegustationSelectionDegustateursForm extends acCouchdbForm {

    protected $degustateurs;
    protected $college;

    public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
        $doc->getOrAdd('degustateurs');
        $this->college = $options['college'];
        parent::__construct($doc, $defaults, $options, $CSRFSecret);
        $defaults = array_merge($this->getDefaults(), $this->getDefaultsByDoc($doc));
        $this->setDefaults($defaults);
    }

	public function configure()
    {
	  $form = new BaseForm();
      $subForm = new BaseForm();

      if ($this->getDocument()->degustateurs->exist($this->college) && count($this->getDocument()->degustateurs->{$this->college})) {
          foreach ($this->getDocument()->degustateurs->{$this->college} as $id => $selectionne) {
              $subForm->embedForm($id, new DegustationSelectionDegustateurForm());
          }
      }

      foreach($this->getDegustateursByCollege() as $compte_id => $compte) {
          $subForm->embedForm($compte->_id, new DegustationSelectionDegustateurForm());
      }
      $form->embedForm($this->college, $subForm);
      $this->embedForm('degustateurs', $form);
      $this->validatorSchema->setPostValidator(new DegustationSelectionDegustateursValidator($this->getDocument(),null, array('college' => $this->college)));
      $this->widgetSchema->setNameFormat('degustation[%s]');
    }

    protected function getDefaultsByDoc($doc)
    {
        $defaults = array();
        foreach($this->getDegustateursByCollege() as $compte_id => $compte) {
                $selectionne = 0;
                if ($doc->degustateurs->exist($this->college) && $doc->degustateurs->{$this->college}->exist($compte_id)) {
                    $selectionne = 1;
                }
                $defaults['degustateurs'][$this->college][$compte_id] = array('selectionne' => $selectionne);

        }
        return $defaults;
    }

    public function save() {
        $values = $this->getValues();
        $doc = $this->getDocument();
        $doc->getOrAdd('degustateurs');

        if ($doc->degustateurs->exist($this->college) === false) {
            $doc->degustateurs->add($this->college, []);
        }

        $degustateurs_actuels = $doc->degustateurs->{$this->college};
        $degustateurs_selectionnes = $values['degustateurs'][$this->college];

        $doc->degustateurs->remove($this->college);

        foreach ($degustateurs_selectionnes as $degustateur_id => $val) {
            if (! $val['selectionne']) {
                continue;
            }

            $degustateur = $doc->addDegustateur($degustateur_id, $this->college);

            if (array_key_exists($degustateur_id, $degustateurs_actuels->toArray(1, 0))) {
                $doc->degustateurs->{$this->college}->{$degustateur_id} = $degustateurs_actuels[$degustateur_id];
            }
        }

        $doc->save();
    }

    public function getDegustateursByCollege() {
        if (!$this->degustateurs) {
            $this->degustateurs = $this->getDocument()->listeDegustateurs($this->college);
        }
        return $this->degustateurs;
    }

    public function getCompteByIdentifiant($identifiant) {
        $comptes = $this->getDegustateursByCollege();
        return (isset($comptes[$identifiant]))? $comptes[$identifiant] : null;
    }

}
