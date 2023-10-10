<?php

class PMCLotForm extends TransactionLotForm
{
    public function configure() {
        parent::configure();

        $this->setWidget('date_degustation_voulue', new sfWidgetFormInput(array(), array()));
        $this->setValidator('date_degustation_voulue', new sfValidatorDate(array('with_time' => false, 'datetime_output' => 'Y-m-d', 'date_format' => '~(?<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => false)));

        $this->setWidget('engagement_8515', new sfWidgetFormInputCheckbox());
        $this->setValidator('engagement_8515', new sfValidatorBoolean());

        if ($this->getObject()->getDocument()->getType() === PMCNCClient::TYPE_MODEL) {
            $this->setWidget('produit_hash', new bsWidgetFormChoice([
                'choices' => $this->getProduits($this->getObject()->getProduitHash())
            ]));
            $this->setValidator('produit_hash', new sfValidatorChoice([
                'required' => false,
                'choices' => array_keys($this->getProduits($this->getObject()->getProduitHash()))
            ]));

            $this->setWidget('millesime', new bsWidgetFormInput());
            $this->setValidator('millesime', new sfValidatorChoice([
                'required' => false,
                'choices' => [$this->getObject()->millesime => $this->getObject()->millesime]
            ]));
        }

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

    public function getProduits($filter_hash = null)
    {
        $produits = [];

        if(!$this->getObject()->getDocument()->getDRev())  {

            return $produits;
        }

        foreach ($this->getObject()->getDocument()->getDRev()->getProduits() as $produit) {
            $produitConfig = $produit->getConfig();
            if ($produitConfig->isActif() === false) {
                continue;
            }

            if ($filter_hash && $filter_hash !== $produit->getProduitHash()) {
                continue;
            }

            if ($this->getObject()->getDocument()->exist('region') && $this->getObject()->getDocument()->region != "") {
                $filtered_produits = array_filter(RegionConfiguration::getInstance()->getOdgProduits($this->getObject()->getDocument()->region), function ($v) use ($produitConfig) {
                    return strpos($produitConfig->getHash(), $v) !== false;
                });

                if (count($filtered_produits) < 1) {
                    continue;
                }
            }

            $produits[$produit->getProduitHash()] = $produit->getLibelleComplet();
        }

        if ($filter_hash) {
            return $produits;
        }

        return array_merge(['' => ''], $produits);
    }
}
