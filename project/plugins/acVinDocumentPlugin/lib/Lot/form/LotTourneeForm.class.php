<?php

class LotTourneeForm extends LotModificationForm
{
    public function configure()
    {
        parent::configure();

        for($i = 0; $i < self::NBCEPAGES; $i++) {
            unset($this['cepage_'.$i]);
            unset($this['repartition_'.$i]);
        }

        unset($this['elevage']);

        $this->setWidget('quantite', new bsWidgetFormInputInteger());
        $this->setValidator('quantite', new sfValidatorInteger(['min' => 1]));
        $this->widgetSchema->setLabel('quantite', 'Cols');
    }

    public function getDestinationsType()
    {
        return array_merge(parent::getDestinationsType(), ['CONDITIONNEMENT_PILE' => 'Conditionné sur pile']);
    }
}
