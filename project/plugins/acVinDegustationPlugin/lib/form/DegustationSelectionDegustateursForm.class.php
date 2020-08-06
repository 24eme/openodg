<?php

class DegustationSelectionDegustateursForm extends acCouchdbForm {
    
    protected $degustateurs;
    
    public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
        parent::__construct($doc, $defaults, $options, $CSRFSecret);
        $doc->add('degustateurs');
    }

	public function configure()
    {
        $subForm = new BaseForm();

        foreach($this->getDegustateursByColleges() as $college => $comptes) {
            foreach ($comptes as $compte) {
                $subForm->embedForm($compte->_id, new DegustationSelectionDegustateurForm($compte));
            }
        }

        $this->embedForm('degustateurs', $subForm);

        $this->widgetSchema->setNameFormat('degustation[%s]');
    }

	public function save() {
		$values = $this->getValues();
	}
    
    public function getDegustateursByColleges() {
        if (!$this->degustateurs) {
            $this->degustateurs = array();
            foreach (DegustationConfiguration::getInstance()->getColleges() as $tag => $libelle) {
                $comptes = CompteTagsView::getInstance()->listByTags('manuel', $tag);
                if (count($comptes) > 0) {
                    $result = array();
                    foreach ($comptes as $compte) {
                        $result[$compte->id] = CompteClient::getInstance()->find($compte->id);
                    }
                    $this->degustateurs[$libelle] = $result;
                }
            }
            ksort($this->degustateurs);
        }
        return $this->degustateurs;
    }

}
