<?php

class LotCommissionForm extends acCouchdbObjectForm
{
    public function configure()
    {
        $this->setWidget('date_commission', new sfWidgetFormChoice([
            'choices' => $this->getDatesCommissionPossible()
        ]));
        $this->setValidator('date_commission', new sfValidatorChoice(['choices' => $this->getDatesCommissionPossible()]));
        $this->widgetSchema->setNameFormat('[%s]');
    }

    private function getDatesCommissionPossible()
    {
        $now = new DateTimeImmutable();

        return array_map(function ($v) use ($now) {
            return $now->modify('+'.$v.' week')->format(DATE_RSS);
        }, range(1, 5));
    }
}
