<?php

class ChgtDenomValidationForm extends acCouchdbForm
{
    public $isAdmin = null;

    public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
      parent::__construct($doc, $defaults, $options, $CSRFSecret);
      $this->isAdmin = $this->getOption('isAdmin') ? $this->getOption('isAdmin') : false;
      $this->updateDefaults();
    }

    public function updateDefaults() {
        $this->setDefault('affectable', $this->getDocument()->get('changement_affectable'));
    }


    public function configure()
    {
        $this->setWidget('affectable', new sfWidgetFormInputCheckbox());
        $this->setValidator('affectable', new sfValidatorBoolean(['required' => false]));

        $this->widgetSchema->setNameFormat('chgt_denom_validation[%s]');
    }

    public function save()
    {
      $values = $this->getValues();
      $dateValidation = date('c');
      if ($this->getDocument()->isApprouve()) {
          throw new sfException("On ne peut pas changer la validation d'un chgt déjà approuvé");
      }
      $this->getDocument()->clearLots();

      if($this->isAdmin) {
          if (isset($values['affectable']) && $values['affectable']) {
              $this->getDocument()->set('changement_affectable', true);
          } else {
              $this->getDocument()->set('changement_affectable', false);
          }
       }

       if($this->getDocument()->isValidee()){
         $this->getDocument()->validateOdg();
       }

      if(!$this->getDocument()->isValidee()){
        $this->getDocument()->validate($dateValidation);
      }

      $this->getDocument()->save();
    }
}
