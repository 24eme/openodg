<?php

class TransactionValidationForm extends acCouchdbForm
{
  public $admin;
  public function __construct(acCouchdbDocument $doc, $admin, $defaults = array(), $options = array(), $CSRFSecret = null) {
    $this->admin = $admin;
    parent::__construct($doc, $defaults, $options, $CSRFSecret);

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

        if($this->admin){
          $formLots = new TransactionLotsForm($this->getDocument());
          $this->embedForm('lots', $formLots);
        }

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

       if($this->admin){
        foreach ($this->getEmbeddedForm('lots')->getEmbeddedForms() as $k => $embedForms) {
          foreach ($embedForms as $key => $embedForm) {

            $this->getDocument()->lots[$key]->set("degustable", $values['lots']['lots'][$key]['degustable']);

            TransactionLotForm::setLotStatut($this->getDocument()->lots[$key], $values['lots']['lots'][$key]);
          }
        }
       }
       $this->getDocument()->save();
  	}
}
