<?php

class LotCommissionForm extends acCouchdbObjectForm
{
    public function configure()
    {
        $this->setWidget('date_commission', new sfWidgetFormChoice([
            'choices' => $this->getDatesCommissionPossible()
        ]));
        $this->setValidator('date_commission', new sfValidatorDate(['date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true]));
        $this->widgetSchema->setNameFormat('[%s]');
    }

    private function getDatesCommissionPossible()
    {
        $now = new DateTimeImmutable();
        $dates = [];

        foreach (range(1, 5) as $week) {
            $dates[$now->modify('+'.$week.' week')->format('d/m/Y')] = $now->modify('+'.$week.' week')->format('d M Y H:i');
        }

        return $dates;
    }

    public function updateDefaultsFromObject()
    {
        parent::updateDefaultsFromObject();

        if ($this->getObject()->date_commission) {
            $this->setDefault('date_commission', DateTimeImmutable::createFromFormat('Y-m-d', $this->getObject()->date_commission)->format('d/m/Y'));
        }
    }
}
