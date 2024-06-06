<?php

class PMCValidationForm extends acCouchdbForm
{
    public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
        parent::__construct($doc, $defaults, $options, $CSRFSecret);
    }

    public function isAdmin() {

        return ($this->getOption('isAdmin'));
    }

    public function configure() {
        if(!$this->getDocument()->isPapier() && !$this->getDocument()->validation) {
            $engagements = $this->getOption('engagements');
            foreach ($engagements as $engagement) {
                $this->setWidget('engagement_'.$engagement->getCode(), new sfWidgetFormInputCheckbox());
                $this->setValidator('engagement_'.$engagement->getCode(), new sfValidatorBoolean(array('required' => true)));
                if (preg_match('/_OUEX_/', $engagement->getCode())) {
                    $this->getValidator('engagement_'.$engagement->getCode())->setOption('required', false);
                }elseif (preg_match('/_OU_/', $engagement->getCode())) {
                    $this->getValidator('engagement_'.$engagement->getCode())->setOption('required', false);
                }
            }
        }

        if($this->isAdmin() && !$this->getDocument()->isValideeODG()){
            $formDegustable = new BaseForm();
            foreach($this->getDocument()->getLotsByCouleur(false) as $couleur => $lots) {
                foreach ($lots as $lot) {
                    $formDegustable->embedForm($lot->getKey(), new LotCommissionForm($lot));
                }
            }

            $this->embedForm('lots', $formDegustable);
        }

        if(!$this->getDocument()->validation && $this->getDocument()->isPapier() && $this->getDocument()->type == PMCClient::TYPE_MODEL) {
            $this->setWidget('date', new sfWidgetFormInput([], ["required" => "required"]));
            $this->setValidator('date', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));
            $this->getWidget('date')->setLabel("Date de réception du document");
            $this->getValidator('date')->setMessage("required", "La date de réception du document est requise");
        }

        $this->widgetSchema->setNameFormat('validation[%s]');
    }

    public function save() {
       $values = $this->getValues();

       if($this->isAdmin()){
         foreach ($this->getEmbeddedForm('lots')->getEmbeddedForms() as $key => $embedForm) {
           $this->getDocument()->lots[$key]->set("date_commission", $values['lots'][$key]['date_commission']);
        }
       }

       $this->getDocument()->save();
  	}
}
