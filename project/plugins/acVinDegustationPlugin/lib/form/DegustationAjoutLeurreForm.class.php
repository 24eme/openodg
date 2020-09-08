<?php
class DegustationAjoutLeurreForm extends acCouchdbObjectForm
{
    protected $produits;

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null)
    {
        $this->produits = array();
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure()
    {
        $produits = $this->getProduits();
        $this->setWidgets(array(
            'hashref' => new sfWidgetFormChoice(array('choices' => $produits))
        ));
        $this->widgetSchema->setLabels(array(
            'hashref' => 'Appellation: '
        ));

        $this->setValidators(array(
            'hashref' => new sfValidatorChoice(array('required' => true, 'choices' => array_keys($produits)),array('required' => "Aucune appellation saisi."))
        ));

        $this->widgetSchema['numero_lot'] = new sfWidgetFormInput();
        $this->widgetSchema['numero_lot']->setLabel("");
        $this->validatorSchema['numero_lot'] = new sfValidatorString(array('required' => false));

        $this->widgetSchema->setNameFormat('degustation_ajout_leurre[%s]');
    }

    public function getProduits()
    {
        if (!$this->produits) {
            $produits = $this->getObject()->getConfigProduits();
            foreach ($produits as $produit) {
                if (!$produit->isActif()) {
                	continue;
                }

                $this->produits[$produit->getHash()] = $produit->getLibelleComplet();
            }
        }
        return array_merge(array('' => ''), $this->produits);
    }

    public function hasProduits()
    {
        return (count($this->getProduits()) > 1);
    }

    protected function doUpdateObject($values)
    {
        if (isset($values['hashref']) && !empty($values['hashref'])) {
          $this->lots->add(null, $lot);
          //addProduit($values['hashref'],$denomination_complementaire);
        }
    }

}
