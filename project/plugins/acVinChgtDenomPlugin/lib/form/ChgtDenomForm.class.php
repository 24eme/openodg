<?php

class ChgtDenomForm extends acCouchdbObjectForm
{
    public static $types = array("CHGT" => "Changement de dénomination", "DCLST" => "Déclassement");
    public static $quantites = array("TOT" => "Totale", "PART" => "Partielle");
    public $firstEdition;

    public function __construct(acCouchdbJson $object, $firstEdition, $options = array(), $CSRFSecret = null) {
        $this->firstEdition = $firstEdition;
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure() {
        $produits = $this->getProduits();

        $this->setWidget('changement_type', new bsWidgetFormChoice(array('choices' => self::$types, 'expanded' => true)));
        $this->setValidator('changement_type', new sfValidatorChoice(array('choices' => array_keys(self::$types), 'required' => true)));

        $this->setWidget('changement_quantite', new bsWidgetFormChoice(array('choices' => self::$quantites, 'expanded' => true)));
        $this->setValidator('changement_quantite', new sfValidatorChoice(array('choices' => array_keys(self::$quantites), 'required' => true)));

        $this->setWidget('changement_volume', new bsWidgetFormInputFloat());
        $this->setValidator('changement_volume', new sfValidatorNumber(array('required' => false)));

        $this->setWidget('changement_produit', new bsWidgetFormChoice(array('choices' => $produits)));
        $this->setValidator('changement_produit', new sfValidatorChoice(array('required' => false, 'choices' => array_keys($produits))));

        $this->validatorSchema->setPostValidator(new ChgtDenomValidator($this->getObject()));
        $this->widgetSchema->setNameFormat('chgt_denom[%s]');
    }

    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);
        if ($values['changement_type'] == 'DCLST') {
          	$this->getObject()->changement_produit = null;
        }
        if ($values['changement_quantite'] == 'TOT') {
          	$this->getObject()->changement_volume = null;
        }
        if ($values['changement_produit']) {
            $produits = $this->getProduits();
          	$this->getObject()->changement_produit_libelle = (isset($produits[$values['changement_produit']]))? $produits[$values['changement_produit']] : null;
        }
    }

    protected function updateDefaultsFromObject() {
      parent::updateDefaultsFromObject();
      $defaults = $this->getDefaults();
      $defaults['changement_type'] = (!$this->firstEdition && !$this->getObject()->changement_produit)? 'DCLST' : 'CHGT';
      $defaults['changement_quantite'] = ($this->getObject()->changement_volume > 0)? 'PART' : 'TOT';
      $this->setDefaults($defaults);
    }

    public function getProduits()
    {
        $produits = array();
        foreach ($this->getObject()->getDocument()->getConfigProduits() as $produit) {
            if (!$produit->isActif()) {
                continue;
            }
            $produits[$produit->getHash()] = $produit->getLibelleComplet();
        }
        return array_merge(array('' => ''), $produits);
    }
}
