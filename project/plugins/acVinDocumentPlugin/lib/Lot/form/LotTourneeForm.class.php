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
    }

    public function getDestinationsType()
    {
        return array_merge(parent::getDestinationsType(), ['CONDITIONNEMENT_PILE' => 'Conditionné sur pile']);
    }
}
