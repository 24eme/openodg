<?php
class DRevUploadDrForm extends FichierForm
{
	public function configure() {
		parent::configure();
		$this->setWidget('libelle', new sfWidgetFormInputHidden());
		$this->setWidget('date_depot', new sfWidgetFormInputHidden());
		$this->setWidget('visibilite', new sfWidgetFormInputHidden());
		$this->widgetSchema->setLabel('file', 'Fichier');
		$required = !($this->options['papier'] || !DrevConfiguration::getInstance()->isDrDouaneRequired());

		$this->setWidget('libelle', new sfWidgetFormInputHidden());
        $this->setValidator('file', new sfValidatorFile(array('required' => $required, 'mime_types' => array('application/vnd.ms-office', 'application/vnd.ms-excel', 'text/csv', 'text/plain', 'text/json', 'application/json'), 'path' => sfConfig::get('sf_cache_dir')), array('mime_types' => 'Fichier de type xls ou csv attendu')));
	}

	public function save() {
		$file = $this->getValue('file');
		if (!$file && $this->fichier->isNew()) {
			throw new sfException("Une erreur lors de l'upload est survenue");
    	}
		$this->fichier->deleteFichier();
		$this->fichier->save();

		return parent::save();
	}
}
