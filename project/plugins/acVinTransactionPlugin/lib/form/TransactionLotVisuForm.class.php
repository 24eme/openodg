<?php
class TransactionLotVisuForm extends acCouchdbObjectForm
{
    const NBCEPAGES = 5;
    public $lot = null;

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
        $this->lot = $object;
        $this->getDocable()->remove();
        $this->getValidatorSchema()->setOption('allow_extra_fields', true);
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
    }

    public function configure() {
        $produits = $this->getProduits();
        $cepages = $this->getCepages();

        $this->setWidget('degustable', new sfWidgetFormInputCheckbox());
        $this->setValidator('degustable', new sfValidatorBoolean(['required' => false]));

        $this->widgetSchema->setNameFormat('[%s]');
    }

    public function doUpdateObject($values) {

				$this->getObject()->getOrAdd("degustable", null);
				$this->getObject()->degustable = $values['degustable'];
				if ($values['degustable']) {
          $this->getObject()->statut = Lot::STATUT_PRELEVABLE;
        }else{
          $this->getObject()->statut = Lot::STATUT_NONPRELEVABLE;
        }

    }

    public function getProduits()
    {
        $produits = array();
        foreach ($this->getObject()->getDocument()->getConfigProduits() as $produit) {
            if(!$produit->isRevendicationParLots()) {
                continue;
            }
            if (!$produit->isActif()) {
                continue;
            }
            $produits[$produit->getHash()] = $produit->getLibelleComplet();
        }
        return array_merge(array('' => ''), $produits);
    }

    public function getCepages()
    {
        return array_merge(array('' => ''), $this->getObject()->getDocument()->getConfiguration()->getCepagesAutorises());
    }

}
