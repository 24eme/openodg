<?php

class LotCommissionForm extends acCouchdbObjectForm
{
    public function configure()
    {
        $this->setWidget('date_commission', new bsWidgetFormInput(array(), array('required' => true)));
        $this->setValidator('date_commission', new sfValidatorDate(array('with_time' => false, 'datetime_output' => 'Y-m-d', 'date_format' => '~(?<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));

        $degustations = self::getDegustationChoices();
        if((!$this->getObject()->exist('date_commission') || !$this->getObject()->date_commission) && count($degustations) > 0) {
            $this->setDefault('date_commission', array_key_first($degustations));
            $this->setWidget('degustation',new bsWidgetFormChoice( array('choices' => $degustations), array('required' => true)));
            $this->setValidator('degustation', new sfValidatorPass(array('required' => false)));
            $this->widgetSchema['date_commission']->setAttribute('required', false);
            $this->getWidget('date_commission')->setAttribute('class', 'form-control hidden');
        } else {
            $this->setDefault('date_commission', date('d/m/Y'));
        }
        $this->widgetSchema->setNameFormat('[%s]');
    }

    public static function getDegustationChoices() {
        $degustations = array();
        $history = DegustationClient::getInstance()->getHistory(10, "", acCouchdbClient::HYDRATE_DOCUMENT, Organisme::getCurrentRegion());

        if (is_array($history) === false) {
            $history = $history->getDatas();
        }

        ksort($history);
        foreach ($history as $degustation_id => $degustation) {
            if($degustation->date < date('Y-m-d')) {
                continue;
            }
            if($degustation->isAnonymized()) {
                continue;
            }
            $date = new DateTime($degustation->date);
            $degustations[$date->format('d/m/Y')] = "Degustation du ".$degustation->getDateFormat('d/m/Y');
        }

        ksort($degustations);

        return array_merge(["" => ""], $degustations);
    }

    public function updateDefaultsFromObject()
    {
        parent::updateDefaultsFromObject();

        if ($this->getObject()->exist('date_commission') && $this->getObject()->date_commission) {
            $this->setDefault('date_commission', DateTime::createFromFormat('Y-m-d', $this->getObject()->date_commission)->format('d/m/Y'));
        }
    }
}
