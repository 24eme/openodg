<?php

class DRevControleExterneForm extends acCouchdbObjectForm
{

    public function configure() {
        if(($this->getObject()->getDocument()->prelevements->exist(Drev::BOUTEILLE_ALSACE))) {
            $form_alsace = new DRevPrelevementForm($this->getObject()->getDocument()->prelevements->get(Drev::BOUTEILLE_ALSACE));
            $this->embedForm(Drev::BOUTEILLE_ALSACE, $form_alsace);
        }

        if(($this->getObject()->getDocument()->prelevements->exist(Drev::BOUTEILLE_GRDCRU))) {
            $form_grdcru = new DRevPrelevementForm($this->getObject()->getDocument()->prelevements->get(Drev::BOUTEILLE_GRDCRU));
            $this->embedForm(Drev::BOUTEILLE_GRDCRU, $form_grdcru);
        }
        $vtsgn = false;
        if ($this->getObject()->getDocument()->isNonRecoltant()) {
        	if ($this->getObject()->getDocument()->declaration->hasVtsgn()) {
        		$vtsgn = true;
        	}
        } else {
        	if ($this->getObject()->getDocument()->prelevements->exist(Drev::BOUTEILLE_VTSGN) || !$this->getObject()->getDocument()->hasDr()) {
        		$vtsgn = true;
        	}
        }
        if($vtsgn) {
        	$form_vtsgn = new DRevPrelevementForm($this->getObject()->getDocument()->prelevements->getOrAdd(Drev::BOUTEILLE_VTSGN));

            $form_vtsgn->setWidget('total_lots', new sfWidgetFormInputText());
            $form_vtsgn->setValidator('total_lots', new sfValidatorNumber(array('required' => true, 'min' => 1),array('required' => 'Champs obligatoire', "min" => "Le nombre de lot doit être strictement supérieur à 0")));
            $form_vtsgn->getWidget('total_lots')->setLabel("Nombre de lots");

            $this->embedForm(Drev::BOUTEILLE_VTSGN, $form_vtsgn);
        }
        if(count($this->getObject()->getDocument()->getEtablissementObject()->chais) > 1) {
            $this->setWidget("chai", new sfWidgetFormChoice(array('choices' => $this->getChaiChoice(), 'expanded' => true, 'renderer_options' => array('formatter' => array($this, 'formatter')))));
            $this->setValidator("chai", new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->getChaiChoice()))));
        }
        $this->widgetSchema->setNameFormat('controle_externe[%s]');
    }

    public function formatter($widget, $inputs) 
    {
        $rows = array();
        foreach ($inputs as $input) {
            $rows[] = $widget->renderContentTag('div', '<label>'  . $input['input'] . strip_tags($input['label'])  . '</label>' , array('class' => 'radio'));
        }

        return!$rows ? '' : implode($widget->getOption('separator'), $rows);
    }

    public function getChaiChoice() {
        $choices = array();
        foreach($this->getObject()->getDocument()->getEtablissementObject()->chais as $chai) {
            $choices[$chai->getKey()] = sprintf("%s %s %s", $chai->adresse, $chai->code_postal, $chai->commune);
        }

        return $choices;
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        if ($this->getObject()->getDocument()->exist('chais')) {
        	if ($this->getObject()->getDocument()->chais->exist(DRev::BOUTEILLE)) {
        		foreach ($this->getObject()->getDocument()->getEtablissementObject()->chais as $chai) {
        			if ($chai->adresse == $this->getObject()->getDocument()->chais->get(DRev::BOUTEILLE)->adresse) {
        				$this->setDefault('chai', $chai->getKey());
        				break;
        			}
        		}
        	}
        }
    }

    public function doUpdateObject($values) 
    {
        foreach ($this->getEmbeddedForms() as $key => $embedForm) {
            $embedForm->doUpdateObject($values[$key]);
        }

        if(isset($values["chai"])) {
            $this->getObject()->getDocument()->chais->set(DRev::BOUTEILLE, $this->getObject()->getDocument()->getEtablissementObject()->chais->get($values["chai"])->toArray(true, false));
        }
    }
}