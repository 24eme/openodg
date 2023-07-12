<?php

class DegustationLotForm extends LotForm
{
    public function configure()
    {
        parent::configure();

        for($i = 0; $i < self::NBCEPAGES; $i++) {
            unset($this['cepage_'.$i]);
            unset($this['repartition_'.$i]);
        }

        $this->widgetSchema->setNameFormat('[%s]');
    }

    public function getProduits()
    {
        $produits = [];

        foreach ($this->getObject()->getDocument()->getConfigProduits() as $produit) {
            if ($produit->isActif() === false) {
                continue;
            }

            $produits[$produit->getHash()] = $produit->getLibelleComplet();
        }

        return array_merge(['' => ''], $produits);
    }

    public function doUpdateObject($values)
    {
        parent::doUpdateObject($values);

        if (! $this->getObject()->preleve) {
            $this->getObject()->set('preleve', date('Y-m-d'));
        }
    }
}
