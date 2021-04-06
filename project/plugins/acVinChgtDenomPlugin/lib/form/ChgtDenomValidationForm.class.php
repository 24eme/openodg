<?php

class ChgtDenomValidationForm extends acCouchdbForm
{
    public $isAdmin = null;
    public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
      parent::__construct($doc, $defaults, $options, $CSRFSecret);
      $this->isAdmin = $this->getOption('isAdmin') ? $this->getOption('isAdmin') : false;
    }
    public function configure() {

        $formaffectable = new BaseForm();

        foreach($this->getDocument()->lots as $key => $lot) {
    				$formaffectable->embedForm($lot->getKey(), new LotAffectableForm($lot));
        }

        $this->embedForm('lots', $formaffectable);

        $this->widgetSchema->setNameFormat('chgt_denom_validation[%s]');
    }

    protected function updateDefaultsFromObject() {
      parent::updateDefaultsFromObject();
    }

    public function save(){
      $values = $this->getValues();
      $dateValidation = date('c');

      if($this->isAdmin){
        foreach ($this->getEmbeddedForm('lots')->getEmbeddedForms() as $key => $embedForm) {
          $this->getDocument()->lots[$key]->set("affectable", $values['lots'][$key]['affectable']);
       }

       if($this->getDocument()->isValidee()){
         $this->getDocument()->validateOdg();
       }
      }

      if(!$this->getDocument()->isValidee()){
        $this->getDocument()->validate($dateValidation);
      }

      $this->getDocument()->save();
    }
}
