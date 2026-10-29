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
            unset($this->widgetSchema['produit_hash']);
            unset($this->validatorSchema['produit_hash']);
            unset($this->widgetSchema['millesime']);
            unset($this->validatorSchema['millesime']);
            unset($this->widgetSchema['engagement_8515']);
            unset($this->validatorSchema['engagement_8515']);
        }

        unset($this->widgetSchema['specificite']);
        unset($this->validatorSchema['specificite']);
        $this->setWidget('specificite', new bsWidgetFormInput(array(), array()));
        $this->setValidator('specificite', new sfValidatorString(array('required' => false)));

        $this->widgetSchema->setNameFormat('[%s]');
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();

        $this->setDefault('date_degustation_voulue', $this->getObject()->getDateDegustationVoulueFr());
    }

    public function doUpdateObject($values) {
        if ($this->getObject()->getDocument()->getType() === PMCNCClient::TYPE_MODEL) {
            $values['produit_hash'] = $this->getObject()->getDocument()->getLotOrigine()->produit_hash;
            $values['produit_libelle'] = $this->getObject()->getDocument()->getLotOrigine()->produit_libelle;
            $values['millesime'] = $this->getObject()->getDocument()->getLotOrigine()->millesime;
        }
        parent::doUpdateObject($values);

        if (!empty($values['date_degustation_voulue'])) {
          $this->getObject()->date_degustation_voulue = $values['date_degustation_voulue'];
        }
    }

    public function getProduits($filter_hash = null)
    {
        $produits = [];

        foreach($this->getObject()->getDocument()->getHabilitation()->getProduits() as $appellation) {
            $appellationConfig = $appellation->getConfig();
            foreach($appellationConfig->getProduitsAll() as $produitConfig) {
                if ($produitConfig->isActif() === false) {
                    continue;
                }

                if ($filter_hash && $filter_hash !== $produitConfig->getHash()) {
                    continue;
                }

                $produits[$produitConfig->getHash()] = $produitConfig->getLibelleComplet();
            }
        }

        if ($filter_hash) {
            return $produits;
        }

        return array_merge(['' => ''], $produits);
    }
}
