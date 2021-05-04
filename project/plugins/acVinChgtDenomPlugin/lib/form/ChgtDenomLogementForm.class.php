<?php

class ChgtDenomLogementForm extends acCouchdbObjectForm
{
    public function configure()
    {
        $chgt = $this->getObject();

        foreach ($chgt->lots as $lot) {
            if ($lot->isLogementEditable() && $lot->isLotOrigine()) {
                $this->setWidget('origine_numero_logement_operateur', new bsWidgetFormInput());
                $this->setValidator('origine_numero_logement_operateur', new sfValidatorString());
            }

            if ($lot->isLogementEditable() && ! $lot->isLotOrigine()) {
                $this->setWidget('changement_numero_logement_operateur', new bsWidgetFormInput());
                $this->setValidator('changement_numero_logement_operateur', new sfValidatorString());
            }
        }

        $this->widgetSchema->setNameFormat('chgt_denom_logement[%s]');
    }
}
