<?php
class TirageValidationForm extends acCouchdbForm 
{    
    public function configure()
    {
        if(!$this->getDocument()->isPapier()) {
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
        }
        
        if($this->getDocument()->isPapier()) {
            $this->setWidget('date', new sfWidgetFormInput());
            $this->setValidator('date', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));
            $this->getWidget('date')->setLabel("Date de réception du document");
            $this->getValidator('date')->setMessage("required", "La date de réception du document est requise");
        }

        $this->widgetSchema->setNameFormat('tirage_validation[%s]');
    }
    
}