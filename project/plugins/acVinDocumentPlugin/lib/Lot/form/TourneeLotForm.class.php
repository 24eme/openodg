<?php

class TourneeLotForm extends LotModificationForm
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
        $this->setValidator('quantite', new sfValidatorInteger(['required' => false]));
        $this->widgetSchema->setLabel('quantite', 'Cols');
    }

    public function getDestinationsType()
    {
        return [
                '' => '',
                DRevClient::LOT_DESTINATION_CONDITIONNEMENT_ENCOURS => 'En cours de conditionnement',
                DRevClient::LOT_DESTINATION_CONDITIONNEMENT =>  'Conditionné sur pile',
                DRevClient::LOT_DESTINATION_VRAC => 'En vrac',
                DRevClient::LOT_DESTINATION_CONDITIONNEMENT_CONSERVATOIRE => 'Conditionné conservatoire'
            ];
    }
}
