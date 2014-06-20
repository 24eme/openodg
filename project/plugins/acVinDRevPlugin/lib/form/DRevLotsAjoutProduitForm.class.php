<?php
class DRevLotsAjoutProduitForm extends acCouchdbObjectForm 
{    
	protected $node;
	protected $produits;
	
	public function __construct(acCouchdbJson $object, $node, $options = array(), $CSRFSecret = null) 
	{
		$this->node = $node;
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
        $this->widgetSchema->setNameFormat('drev_lots_ajout_produit[%s]');
    }
    
	public function getProduits() 
    {
    	if (!$this->produits) {
    		$configuration = $this->getObject()->getConfiguration();
    		$produits = $configuration->getDrevLotProduits(str_replace(DRev::PREFIXE_LOT_KEY, '', $this->node));
	    	foreach ($produits as $produit) {
		    	if ($this->node == DRev::PREFIXE_LOT_KEY.Drev::PRODUITS_LOT_GRDCRU_CONFIGURATION_KEY && preg_match('/\/lieu\//', $produit)) {
	    			continue;
	    		}
	    		$nodeHash = str_replace('/', '_', $produit); 
	    		if (!$this->getObject()->lots->getOrAdd($this->node)->produits->exist($nodeHash)) {
	    			$libelle = '';
			    	if ($configuration->get($produit)->getLieu()->libelle) {
			    		$libelle .= $configuration->get($produit)->getLieu()->libelle.' - ';
			    	}
			    	$libelle .= $configuration->get($produit)->libelle;
	    			$this->produits[$produit] = $libelle;
	    		}
	    	}
    	}
    	return array_merge(array('' => ''), $this->produits);
    }
    
    public function hasProduits()
    {
    	return (count($this->getProduits()) > 0);
    }
    
    protected function doUpdateObject($values)
    {
    	if (isset($values['hashref']) && !empty($values['hashref'])) {
    		$this->getObject()->addLotProduit($this->getObject()->lots->getOrAdd($this->node), $values['hashref']);
    	}
    }
}