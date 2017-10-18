<?php
class HabilitationAjoutProduitForm extends acCouchdbObjectForm
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
            'hashref' => new sfWidgetFormChoice(array('choices' => $produits)),
        ));
        $this->widgetSchema->setLabels(array(
            'hashref' => 'Produit: ',
        ));

        $this->setValidators(array(
            'hashref' => new sfValidatorChoice(array('required' => true, 'choices' => array_keys($produits)),array('required' => "Aucun produit saisi.")),
        ));

        $this->widgetSchema->setNameFormat('habilitation_cepage_ajout_produit[%s]');
    }

    public function getProduits()
    {
        if (!$this->produits) {
            $doc = $this->getObject()->getDocument();
            foreach ($this->getObject()->getConfiguration()->getProduitsCahierDesCharges() as $produit) {
                if ($this->getObject()->exist($produit->getHash())) {
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
        if (!isset($values['hashref']) || empty($values['hashref'])) {

            return;
        }

        $noeud = $this->getObject()->getDocument()->addProduit($values['hashref']);
    }
}
