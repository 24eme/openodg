<?php

class FactureEditionForm extends acCouchdbObjectForm {

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();

        $date = new DateTime($this->getObject()->getDateFacturation());

        $this->setDefault('date_facturation', $date->format('d/m/Y'));
    }

    public function configure()
    {
        $this->setWidget('date_facturation', new sfWidgetFormInput(array(), array()));
        $this->setValidator('date_facturation', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));


        $this->getObject()->lignes->add("nouvelle");
        $this->embedForm('lignes', new FactureEditionLignesForm($this->getObject()->lignes));

        $this->widgetSchema->setNameFormat('facture_edition[%s]');
    }

    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);

        if($this->getObject()->lignes->exist("nouvelle")) {
            $newLine = $this->getObject()->lignes->get("nouvelle")->toArray(true, false);
            $this->getObject()->lignes->remove("nouvelle");
            $this->getObject()->lignes->add(uniqid(), $newLine);
        }

        $this->getObject()->lignes->cleanLignes();
        $this->getObject()->updateTotaux();
    }

    /*public function processValues($values) {
        parent::processValues($values);
        foreach($values['lignes'] as $key_ligne => $ligne) {
            foreach($ligne['details'] as $key_detail => $detail) {
                if(empty($detail['quantite']) && empty($detail['libelle']) && empty($detail['prix_unitaire'])) {
                    unset($values['lignes'][$key_ligne]['details'][$key_detail]);
                }
            }
        }
        return $values;
    }*/

}
