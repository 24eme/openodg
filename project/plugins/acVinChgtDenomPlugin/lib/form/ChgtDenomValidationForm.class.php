<?php

class ChgtDenomValidationForm extends acCouchdbForm
{
    public $isAdmin = null;
    public $withDate = null;

    public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
      $this->isAdmin = (isset($options['isAdmin']) && $options['isAdmin']);
      $this->withDate = (isset($options['withDate']) && $options['withDate']);
      parent::__construct($doc, $defaults, $options, $CSRFSecret);
      $this->updateDefaults();
    }

    public function updateDefaults() {
        $this->setDefault('affectable', $this->getDocument()->get('changement_affectable'));
    }


    public function configure()
    {
        $this->setWidget('affectable', new sfWidgetFormInputCheckbox());
        $this->setValidator('affectable', new sfValidatorBoolean(['required' => false]));
        if ($this->withDate) {
            $this->setWidget('validation', new sfWidgetFormInput([], ['required' => true]));
            $this->setValidator('validation', new sfValidatorDate(['date_output' => 'c', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true, 'with_time' => false]));
            $this->widgetSchema->setLabel('validation', "Date de validation");
        }

        $this->widgetSchema->setNameFormat('chgt_denom_validation[%s]');
    }

    public function save()
    {
      $values = $this->getValues();
      $dateValidation = $values['validation'];
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
