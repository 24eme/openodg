<?php

class ChgtDenomValidationForm extends acCouchdbForm
{
    public $isAdmin = null;
    public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
      parent::__construct($doc, $defaults, $options, $CSRFSecret);
      $this->isAdmin = $this->getOption('isAdmin') ? $this->getOption('isAdmin') : false;
    }
    public function configure() {
        $this->setWidget('validation', new sfWidgetFormInputHidden());
        $this->setValidator('validation', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<year>\d{4})-(?P<month>\d{2})-(?P<day>\d{2})~', 'required' => true)));

        $formaffectable = new BaseForm();

        foreach($this->getDocument()->lots as $key => $lot) {
    				$formaffectable->embedForm($lot->getKey(), new LotAffectableForm($lot));
        }

        $this->embedForm('lots', $formaffectable);

        $this->widgetSchema->setNameFormat('chgt_denom_validation[%s]');
    }

    protected function updateDefaultsFromObject() {
      parent::updateDefaultsFromObject();
      $defaults = $this->getDefaults();
      $defaults['validation'] = date('Y-m-d');
      $this->setDefaults($defaults);
      exit;
      // if($this->isAdmin){
      //   foreach ($this->getEmbeddedForm('lots')->getEmbeddedForms() as $key => $embedForm) {
      //     $$this->getObject()->lots[$key]->set("affectable", $values['lots'][$key]['affectable']);
      //  }
      // }
    }
}
