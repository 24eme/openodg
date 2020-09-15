<?php

class DegustationSelectionDegustateursForm extends acCouchdbForm {

    protected $degustateurs;

    public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
        parent::__construct($doc, $defaults, $options, $CSRFSecret);
        $doc->getOrAdd('degustateurs');
        $defaults = array_merge($this->getDefaults(), $this->getDefaultsByDoc($doc));
        $this->setDefaults($defaults);
    }

	public function configure()
    {
	    $form = new BaseForm();
        foreach($this->getDegustateursByColleges() as $college => $comptes) {
            $subForm = new BaseForm();
            foreach ($comptes as $compte) {
                $subForm->embedForm($compte->_id, new DegustationSelectionDegustateurForm());
            }
            $form->embedForm($college, $subForm);
        }
        $this->embedForm('degustateurs', $form);
        $this->widgetSchema->setNameFormat('degustation[%s]');
    }

    protected function getDefaultsByDoc($doc)
    {
        $defaults = array();
        foreach($this->getDegustateursByColleges() as $college => $comptes) {
            foreach ($comptes as $compte) {
                $idCompte = $compte->_id;
                $selectionne = 0;
                if ($doc->degustateurs->exist($college) && $doc->degustateurs->{$college}->exist($idCompte)) {
                    $selectionne = 1;
                }
                $defaults['degustateurs'][$college][$idCompte] = array('selectionne' => $selectionne);
            }
        }
        return $defaults;
    }

	public function save() {
		$values = $this->getValues();
		$doc = $this->getDocument();
		$doc->remove('degustateurs');
		$doc->add('degustateurs');
		foreach ($values['degustateurs'] as $college => $items) {
		    foreach ($items as $compteId => $val) {
    		    if (isset($val['selectionne']) && !empty($val['selectionne'])) {
    		        $compte = $this->getCompteByCollegeAndIdentifiant($college, $compteId);
    		        $degustateur = $doc->degustateurs->getOrAdd($college)->add($compteId);
                $degustateur->add('libelle',$compte->getLibelleWithAdresse());
    		    }
		    }
		}
		$doc->save();
	}

    public function getDegustateursByColleges() {
        if (!$this->degustateurs) {
            $this->degustateurs = array();
            foreach (DegustationConfiguration::getInstance()->getColleges() as $tag => $libelle) {
                $comptes = CompteTagsView::getInstance()->listByTags('automatique', $tag);
                if (count($comptes) > 0) {
                    $result = array();
                    foreach ($comptes as $compte) {
                        $result[$compte->id] = CompteClient::getInstance()->find($compte->id);
                    }
                    $this->degustateurs[$tag] = $result;
                }
            }
            ksort($this->degustateurs);
        }
        return $this->degustateurs;
    }

    public function getCompteByCollegeAndIdentifiant($college, $identifiant) {
        $comptes = $this->getDegustateursByColleges();
        return (isset($comptes[$college]) && isset($comptes[$college][$identifiant]))? $comptes[$college][$identifiant] : null;
    }

}
