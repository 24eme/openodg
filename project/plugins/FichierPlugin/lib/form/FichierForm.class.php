<?php

class FichierForm extends BaseForm
{
	protected $fichier;

	public function __construct($fichier, $defaults = array(), $options = array(), $CSRFSecret = null)
	{
		$this->fichier = $fichier;
		if ($this->fichier && !$this->fichier->isNew()) {
			$defaults['libelle'] = $this->fichier->getLibelle();
			$defaults['categorie'] = $this->fichier->getCategorie();
			$defaults['date_depot'] = $this->fichier->getDateDepotFormat();
			$defaults['visibilite'] = ($this->fichier->getVisibilite())? 1 : null;
		} else {
			$defaults['date_depot'] = date('d/m/Y');
			$defaults['visibilite'] = 1;
		}
		$this->options = $options;
		parent::__construct($defaults, $options, $CSRFSecret);
	}

     public function configure() {

     	$this->setWidgets(array(
     		'file' => new sfWidgetFormInputFile(array('label' => 'Document')),
			'libelle' => new sfWidgetFormInputText(),
            'categorie' => new bsWidgetFormChoice(array('choices' => $this->getCategories())),
     		'date_depot' => new sfWidgetFormInput(array(), array("data-date-defaultDate" => date('Y-m-d'))),
     		'visibilite' => new sfWidgetFormInputCheckbox()
     	));
     	$fileRequired = (!$this->fichier || $this->fichier->isNew())? true : false;
     	$this->setValidators(array(
     		'file' => new sfValidatorFile(array('required' => $fileRequired, 'path' => sfConfig::get('sf_cache_dir'))),
     		'libelle' => new sfValidatorString(array('required' => true)),
            'categorie' => new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->getCategories()))),
     		'date_depot' => new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)),
     		'visibilite' => new ValidatorBoolean()
     	));

     	$this->widgetSchema->setLabels(array(
     		'file' => ($fileRequired)? 'Fichier*' : 'Fichier',
     		'libelle' => 'Libellé du document*',
     		'categorie' => 'Catégorie',
     		'date_depot' => 'Date dépôt*',
     		'visibilite' => 'Visible par le déclarant'
     	));

        $this->widgetSchema->setNameFormat('fichier[%s]');
    }

	public function getCategories() {
		if($this->getOption('categories')) {

			return $this->getOption('categories');
		}

		return array_merge(array("" => "Fichier"), FichierClient::getInstance()->getCategories());
	}

    public function getFichier() {

        return $this->fichier;
    }

    public function save() {

    	$file = $this->getValue('file');
    	if (!$file && $this->fichier->isNew()) {
    		throw new sfException("Une erreur lors de l'upload est survenue");
    	}
    	if ($file && !$file->isSaved()) {
    		$file->save();
    	}

    	$this->fichier->setLibelle($this->getValue('libelle'));
    	$this->fichier->setDateDepot($this->getValue('date_depot'));
    	$this->fichier->setCategorie($this->getValue('categorie'));
    	$this->fichier->setVisibilite(($this->getValue('visibilite'))? 1 : 0);
    	$isNew = false;
    	if ($this->fichier->isNew()) {
    		$this->fichier->save();
    		$isNew = true;
    	}
    	if ($file) {
	    	try {
				$forceExtension = null;
				if($file->getOriginalExtension() == '.csv' && $file->getExtension() == '.txt') {
					$forceExtension = 'csv';
				}
	    		$this->fichier->storeFichier($file->getSavedName(), $forceExtension);
	    	} catch (sfException $e) {
	    		if ($isNew) {
	    			$this->fichier->remove();
	    		}
	    		throw new sfException($e);
	    	}
    		unlink($file->getSavedName());
    	}
        $this->fichier->constructId();
    	$this->fichier->save();
    	return $this->fichier;
    }


}
