<?php

class DRevValidationForm extends acCouchdbForm
{

    public function configure() {
    	$engagements = $this->getOption('engagements');
  		foreach ($engagements as $engagement) {
  			$this->setWidget('engagement_'.$engagement->getCode(), new sfWidgetFormInputCheckbox());
  			$this->setValidator('engagement_'.$engagement->getCode(), new sfValidatorBoolean(array('required' => true)));
  			if ($engagement->getCode() == DRevDocuments::DOC_DR && $this->getDocument()->hasDr()) {
  				$this->setDefault('engagement_'.$engagement->getCode(), 1);
  				$this->getWidget('engagement_'.$engagement->getCode())->setAttribute('disabled', 'disabled');
  				$this->getValidator('engagement_'.$engagement->getCode())->setOption('required', false);
  				
  			}
  		}
        $this->widgetSchema->setNameFormat('validation[%s]');
    }

}