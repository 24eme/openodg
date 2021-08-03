<?php

class ChgtDenomLogementForm extends acCouchdbObjectForm
{
    public function configure()
    {
        $chgt = $this->getObject();
        $this->has_lots = true;
        if (!$chgt->exist('lots') || !count($chgt->lots)) {
            $this->has_lots = false;
            $chgt->generateLots();
        }

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
        if (!$this->has_lots) {
            $chgt->clearLots();
        }

        $this->widgetSchema->setNameFormat('chgt_denom_logement[%s]');
    }
}
