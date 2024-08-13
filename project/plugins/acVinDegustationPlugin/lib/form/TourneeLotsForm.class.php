<?php

class TourneeLotsForm extends acCouchdbForm
{
    public function configure()
    {
        $formLots = new BaseForm();

        $lots = $this->getDocument()->lots->toArray();
        usort($lots, function ($lot1, $lot2) {
            return $lot1->declarant_nom < $lot2->declarant_nom;
        });

        foreach ($lots as $lot) {
            if ($lot->getLotProvenance()) {
                continue;
            }

            $formLots->embedForm($lot->getKey(), new TourneeLotForm($lot));
        }

        $this->embedForm('lots', $formLots);
        $this->widgetSchema->setNameFormat('degustation_lots[%s]');
    }

    public function save() {
        $values = $this->getValues();
        foreach ($this->getEmbeddedForm('lots')->getEmbeddedForms() as $key => $embedForm) {
            if (! $embedForm->getObject()->preleve) {
                $embedForm->getObject()->setIsPreleve(date('Y-m-d'));
            }
            $embedForm->doUpdateObject($values['lots'][$key]);
        }
        $this->getDocument()->cleanLotsSansProduit();
        $this->getDocument()->save();
    }
}
