<?php

class DegustationLotsForm extends acCouchdbForm
{
    public function configure()
    {
        $formLots = new BaseForm();

        $lots = $this->getDocument()->lots->toArray();
        usort($lots, function ($lot1, $lot2) {
            return $lot1->declarant_nom < $lot2->declarant_nom;
        });

        foreach ($lots as $lot) {
            $formLots->embedForm($lot->getKey(), new LotTourneeForm($lot));
        }

        $this->embedForm('lots', $formLots);
        $this->widgetSchema->setNameFormat('degustation_lots[%s]');
    }

    public function save() {
        $values = $this->getValues();
        foreach ($this->getEmbeddedForm('lots')->getEmbeddedForms() as $key => $embedForm) {
            if (! $embedForm->getObject()->preleve) {
                $embedForm->getObject()->setIsPreleve(date('Y-m-d'));
                $embedForm->getObject()->document_ordre = "01";
            }
            $embedForm->doUpdateObject($values['lots'][$key]);
        }
        $this->getDocument()->cleanLotsSansProduit();
        $this->getDocument()->archiverLot($this->getDocument()->numero_archive);
        $this->getDocument()->save();
    }
}
