<?php

class PMCLotForm extends TransactionLotForm
{
    public function configure() {
        parent::configure();

        $this->setWidget('date_degustation_voulue', new sfWidgetFormInput(array(), array()));
        $this->setValidator('date_degustation_voulue', new sfValidatorDate(array('with_time' => false, 'datetime_output' => 'Y-m-d', 'date_format' => '~(?<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => false)));

        $this->setWidget('engagement_8515', new sfWidgetFormInputCheckbox());
        $this->setValidator('engagement_8515', new sfValidatorBoolean());

        $this->widgetSchema->setNameFormat('[%s]');
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();

        $this->setDefault('date_degustation_voulue', $this->getObject()->getDateDegustationVoulueFr());
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);

        if (!empty($values['date_degustation_voulue'])) {
          $this->getObject()->date_degustation_voulue = $values['date_degustation_voulue'];
        }
    }

    public function getProduits()
    {
        $produits = [];

        foreach ($this->getObject()->getDocument()->getConfigProduits() as $produit) {
            if ($produit->isActif() === false) {
                continue;
            }

            if ($this->getObject()->getDocument()->exist('region') && $this->getObject()->getDocument()->region != "") {
                $filtered_produits = array_filter(RegionConfiguration::getInstance()->getOdgProduits($this->getObject()->getDocument()->region), function ($v) use ($produit) {
                    return strpos($produit->getHash(), $v) !== false;
                });

                if (count($filtered_produits) < 1) {
                    continue;
                }
            }

            $produits[$produit->getHash()] = $produit->getLibelleComplet();
        }

        return array_merge(['' => ''], $produits);
    }
}
