<?php

class ConditionnementValidationForm extends acCouchdbForm
{
  public $isAdmin = null;
  public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
    parent::__construct($doc, $defaults, $options, $CSRFSecret);
    $this->isAdmin = $this->getOption('isAdmin') ? $this->getOption('isAdmin') : false;
  }

    public function configure() {
        if(!$this->getDocument()->isPapier()) {
            $engagements = $this->getOption('engagements');
            foreach ($engagements as $engagement) {
                $this->setWidget('engagement_'.$engagement->getCode(), new sfWidgetFormInputCheckbox());
                $this->setValidator('engagement_'.$engagement->getCode(), new sfValidatorBoolean(array('required' => true)));
                if (preg_match('/_OUEX_/', $engagement->getCode())) {
                    $this->getValidator('engagement_'.$engagement->getCode())->setOption('required', false);
                }
            }
        }

        $formaffectable = new BaseForm();
        foreach($this->getDocument()->getLotsByCouleur(false) as $couleur => $lots) {
            foreach ($lots as $lot) {
                if($lot->hasBeenEdited()){
                    continue;
                }
								$formaffectable->embedForm($lot->getKey(), new LotAffectableForm($lot));
            }
        }

        $this->embedForm('lots', $formaffectable);

        if($this->getDocument()->isPapier()) {
            $this->setWidget('date', new sfWidgetFormInput());
            $this->setValidator('date', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));
            $this->getWidget('date')->setLabel("Date de réception du document");
            $this->getValidator('date')->setMessage("required", "La date de réception du document est requise");
        }

        $this->widgetSchema->setNameFormat('validation[%s]');
    }

    public function save() {
       $values = $this->getValues();
  	   $this->getDocument()->getOrAdd("date_degustation_voulue");
       $this->getDocument()->date_degustation_voulue = date("d/m/y");

       if($this->isAdmin){
         foreach ($this->getEmbeddedForm('lots')->getEmbeddedForms() as $key => $embedForm) {
           $this->getDocument()->lots[$key]->set("affectable", $values['lots'][$key]['affectable']);
        }
       }

       $this->getDocument()->save();
  	}
}
