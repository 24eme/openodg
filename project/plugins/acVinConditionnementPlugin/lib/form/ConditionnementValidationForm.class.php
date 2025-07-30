<?php

class ConditionnementValidationForm extends acCouchdbForm
{
    public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
        parent::__construct($doc, $defaults, $options, $CSRFSecret);
    }

    public function isAdmin() {

        return $this->getOption('isAdmin') ? $this->getOption('isAdmin') : false;
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

        $formDegustable = new BaseForm();
        foreach($this->getDocument()->getLotsByCouleur(false) as $couleur => $lots) {
            foreach ($lots as $lot) {
                if($lot->hasBeenEdited()){
                    continue;
                }
								$formDegustable->embedForm($lot->getKey(), new LotAffectableForm($lot));
            }
        }

        $this->embedForm('lots', $formDegustable);

        if (DRevConfiguration::getInstance()->hasDegustation() && !$this->getDocument()->validation_odg) {
            $this->setWidget('date_commission', new bsWidgetFormInput(array(), array('required' => true)));
            $this->setValidator('date_commission', new sfValidatorDate(array('with_time' => false, 'datetime_output' => 'Y-m-d', 'date_format' => '~(?<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));

            $degustations = DRevValidationForm::getDegustationChoices();
            if ($this->getDocument()->exist('date_commission') && $this->getDocument()->date_commission) {
                $this->setDefault('date_commission', DateTime::createFromFormat('Y-m-d', $this->getDocument()->date_commission)->format('d/m/Y'));
            } elseif(count($degustations) > 0) {
                $this->setDefault('date_commission', array_key_first($degustations));
                $this->setWidget('degustation',new bsWidgetFormChoice( array('choices' => $degustations), array('required' => true)));
                $this->setValidator('degustation', new sfValidatorPass(array('required' => false)));
                $this->widgetSchema['date_commission']->setAttribute('required', false);
                $this->getWidget('date_commission')->setAttribute('class', 'form-control hidden');
            } else {
                $this->setDefault('date_commission', date('d/m/Y'));
            }

            $this->setWidget('date_degustation_voulue', new bsWidgetFormInput(array(), array('required' => true)));
            $this->setValidator('date_degustation_voulue', new sfValidatorDate(array('with_time' => false, 'datetime_output' => 'Y-m-d', 'date_format' => '~(?<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));
            if ($this->getDocument()->exist('date_degustation_voulue') && $this->getDocument()->date_degustation_voulue) {
                $this->setDefault('date_degustation_voulue', DateTime::createFromFormat('Y-m-d', $this->getDocument()->date_degustation_voulue)->format('d/m/Y'));
            } else {
                $this->setDefault('date_commission', date('d/m/Y'));
            }

            if(!$this->getDocument()->validation && $this->getDocument()->isPapier()) {
                $this->setWidget('date', new sfWidgetFormInput());
                $this->setValidator('date', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));
                $this->getWidget('date')->setLabel("Date de réception du document");
                $this->getValidator('date')->setMessage("required", "La date de réception du document est requise");
            }
        }

        $this->widgetSchema->setNameFormat('validation[%s]');
    }

    public function save() {
       $values = $this->getValues();

       if (DRevConfiguration::getInstance()->hasDegustation()) {
           $this->getDocument()->add('date_commission', $values['date_commission']);
           $this->getDocument()->add('date_degustation_voulue', $values['date_degustation_voulue']);
       }

       if($this->isAdmin()){
         foreach ($this->getEmbeddedForm('lots')->getEmbeddedForms() as $key => $embedForm) {
           $this->getDocument()->lots[$key]->set("affectable", $values['lots'][$key]['affectable']);
        }
       }

       $this->getDocument()->save();
  	}
}
