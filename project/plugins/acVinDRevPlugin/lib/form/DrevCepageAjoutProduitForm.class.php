<?php
class DrevCepageAjoutProduitForm extends acCouchdbObjectForm 
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
            'hashref' => 'Produit: '
        ));

        $this->setValidators(array(
            'hashref' => new sfValidatorChoice(array('required' => true, 'choices' => array_keys($produits)),array('required' => "Aucun produit saisi."))
        ));
        $this->widgetSchema->setNameFormat('drev_cepage_ajout_produit[%s]');
    }
    
    public function getProduits() 
    {
        if (!$this->produits) {
            $produits = $this->getObject()->getConfigProduits();
            $doc = $this->getObject()->getDocument();
            foreach ($produits as $produit) {
                if ($doc->exist($produit->getHash())) {
                    continue;
                } 
                $libelle = '';
                if ($produit->getLieu()->libelle) {
                    $libelle .= $produit->getLieu()->libelle.' - ';
                }
                $libelle .= $produit->libelle_long;
                $this->produits[$produit->getHash()] = $libelle;
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

        $noeud = $this->getObject()->getDocument()->addProduitCepage($values['hashref']);
        $noeud->getParent()->reorderByConf();
    }
}