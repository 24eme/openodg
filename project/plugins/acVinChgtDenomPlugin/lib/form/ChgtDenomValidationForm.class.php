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

        foreach ($this->getOption('engagements') as $engagement) {
            $this->setWidget('engagement_'.$engagement->getCode(), new sfWidgetFormInputCheckbox());
            if (strpos($engagement->getCode(), '_OUEX_') !== false) {
                $this->setValidator('engagement_'.$engagement->getCode(), new sfValidatorBoolean(array('required' => false)));
            } elseif (strpos($engagement->getCode(), '_OU_') !== false) {
                $this->setValidator('engagement_'.$engagement->getCode(), new sfValidatorBoolean(array('required' => false)));
            } else {
                $this->setValidator('engagement_'.$engagement->getCode(), new sfValidatorBoolean(array('required' => true)));
            }
        }

        $this->widgetSchema->setNameFormat('chgt_denom_validation[%s]');

        $this->validatorSchema->setPostValidator(
            new sfValidatorCallback(array('callback' => array($this, 'checkEngagements')))
        );
    }

    public function checkEngagements($validator, $values)
    {
        $checked = [];

        foreach ($values as $key => $value) {
            if (strpos($key, 'engagement_') === false) {
                continue;
            }

            if (strpos($key, '_OU_') !== false) {
                if (array_key_exists('OU', $checked) === false) {
                    $checked['OU'] = 0;
                }

                if($value === true) { $checked['OU']++; }
            }

            if (strpos($key, '_OUEX_') !== false) {
                if (array_key_exists('OUEX', $checked) === false) {
                    $checked['OUEX'] = 0;
                }

                if ($value === true) { $checked['OUEX']++; }
            }
        }

        if (array_key_exists('OU', $checked) === true && $checked['OU'] < 1) {
            throw new sfValidatorError($validator, 'Il faut sélectionner au moins un engagement');
        }

        if (array_key_exists('OUEX', $checked) === true && $checked['OUEX'] !== 1) {
            throw new sfValidatorError($validator, 'Il ne faut sélectionner qu\'un engagement');
        }
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
