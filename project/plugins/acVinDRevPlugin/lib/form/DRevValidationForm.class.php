<?php

class DRevValidationForm extends acCouchdbForm
{
    public $isAdmin = null;
    public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
      parent::__construct($doc, $defaults, $options, $CSRFSecret);
      $this->isAdmin = $this->getOption('isAdmin') ? $this->getOption('isAdmin') : false;
    }
    public function configure() {
        $this->isAdmin = $this->getOption('isAdmin');
        if(!$this->getDocument()->isPapier() && !$this->getDocument()->validation) {
          if(!$this->isAdmin){
            $engagements = $this->getOption('engagements');
            foreach ($engagements as $engagement) {
                $this->setWidget('engagement_'.$engagement->getCode(), new sfWidgetFormInputCheckbox());
                $this->setValidator('engagement_'.$engagement->getCode(), new sfValidatorBoolean(array('required' => true)));

                if (preg_match('/_OUEX_/', $engagement->getCode())) {
                    $this->getValidator('engagement_'.$engagement->getCode())->setOption('required', false);
                }
            }
          }

            if (DrevConfiguration::getInstance()->hasDegustation()) {
                $this->setWidget('date_degustation_voulue', new sfWidgetFormInput(array(), array()));
                $this->setValidator('date_degustation_voulue', new sfValidatorDate(array('with_time' => false, 'datetime_output' => 'Y-m-d', 'date_format' => '~(?<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));

                if ($this->getDocument()->exist('date_degustation_voulue') && $this->getDocument()->date_degustation_voulue !== null) {
                    $this->setDefault('date_degustation_voulue', DateTime::createFromFormat('Y-m-d', $this->getDocument()->date_degustation_voulue)->format('d/m/Y'));
                } elseif ($this->getDocument()->isPapier() || $this->isAdmin) {
                    $this->setDefault('date_degustation_voulue', (new DateTime())->format('d/m/Y'));
                }
            }
        }

        if (DrevConfiguration::getInstance()->hasDegustation() && !$this->getDocument()->validation_odg && $this->isAdmin) {
            $this->setWidget('date_commission', new bsWidgetFormInput(array(), array('required' => true)));
            $this->setValidator('date_commission', new sfValidatorDate(array('with_time' => false, 'datetime_output' => 'Y-m-d', 'date_format' => '~(?<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));

            $degustations = self::getDegustationChoices();
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
        }

        if(!$this->getDocument()->validation && $this->getDocument()->isPapier()) {
            $this->setWidget("date", new sfWidgetFormInput());
            $this->setValidator("date", new sfValidatorDate(
                array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true),
                array('required' => 'La date de réception du document est requise')
            ));
            $this->getWidget("date")->setLabel("Date de réception du document");
        }

        if(!$this->getDocument()->validation_odg && $this->isAdmin) {
            $formDegustable = new BaseForm();
            foreach($this->getDocument()->getLotsByCouleur(false) as $couleur => $lots) {
                foreach ($lots as $lot) {
                    if($lot->id_document != $this->getDocument()->_id) {
                        continue;
                    }
                    $formDegustable->embedForm($lot->getKey(), new LotAffectableForm($lot));
                }
            }
            $this->embedForm('lots', $formDegustable);
        }


        $this->widgetSchema->setNameFormat('validation[%s]');
    }

    public function save() {
       $values = $this->getValues();

        if (DrevConfiguration::getInstance()->hasDegustation() && $this->isAdmin) {
            $this->getDocument()->add('date_commission', $values['date_commission']);
        }

        if($this->isAdmin){
          foreach ($this->getEmbeddedForm('lots')->getEmbeddedForms() as $key => $embedForm) {
            $this->getDocument()->lots[$key]->set("affectable", $values['lots'][$key]['affectable']);
         }
        }

       $this->getDocument()->save();
  	}

    public static function getDegustationChoices() {
        $degustations = array();
        $history = DegustationClient::getInstance()->getHistory(10)->getDatas();
        ksort($history);
        foreach ($history as $degustation_id => $degustation) {
            if($degustation->date < date('Y-m-d')) {
                continue;
            }
            if($degustation->isAnonymized()) {
                continue;
            }
            $date = new DateTime($degustation->date);
            $degustations[$date->format('d/m/Y')] = "Degustation du ".$degustation->getDateFormat('d/m/Y')." au ".$degustation->lieu;
        }

        return $degustations;
    }
}
