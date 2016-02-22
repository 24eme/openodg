<?php
class TirageLotsForm extends acCouchdbObjectForm 
{

    public function configure() 
    {
       $this->setWidgets(array(
            'date_mise_en_bouteille_debut' => new sfWidgetFormInput(array(), array("data-date-defaultDate" => date('Y-m-d'))),
       		'date_mise_en_bouteille_fin' => new sfWidgetFormInput(array(), array("data-date-defaultDate" => date('Y-m-d'))),
        ));

        $this->setValidators(array(
            'date_mise_en_bouteille_debut' => new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true), array('required' => "Merci de saisir au moins une date de début d'embouteillage")),
        	'date_mise_en_bouteille_fin' => new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => false)),
        ));
        
        $compositions = new TirageLotsCollectionForm($this->getObject()->composition);
        $this->embedForm('composition', $compositions);

        $this->widgetSchema->setNameFormat('tirage_lots[%s]');
    }

    protected function updateDefaultsFromObject() 
    {
        parent::updateDefaultsFromObject();
            $this->setDefault('date_mise_en_bouteille_debut', $this->getObject()->getDateMiseEnBouteilleDebutFr());
            $this->setDefault('date_mise_en_bouteille_fin', $this->getObject()->getDateMiseEnBouteilleFinFr());
    }

    public function bind(array $taintedValues = null, array $taintedFiles = null)
    {
        foreach ($this->embeddedForms as $key => $form) {
            if($form instanceof FormBindableInterface) {
                $form->bind($taintedValues[$key], $taintedFiles[$key]);
                $this->updateEmbedForm($key, $form);
            }
        }
        parent::bind($taintedValues, $taintedFiles);
    }

    public function updateEmbedForm($name, $form) {
        $this->widgetSchema[$name] = $form->getWidgetSchema();
        $this->validatorSchema[$name] = $form->getValidatorSchema();
    }
    
    public function getFormTemplateComposition()
    {
    	$object = $this->getObject()->composition->add();
    	$form_embed = new TirageLotForm($object);
    	$form = new TirageLotsCollectionTemplateForm($this, 'composition', $form_embed, 'var---nbItem---');
    
    	return $form->getFormTemplate();
    }

}
