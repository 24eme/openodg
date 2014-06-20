<?php
class DRevLotsForm extends acCouchdbObjectForm 
{    
	protected $node;
	
	public function __construct(acCouchdbJson $object, $node, $options = array(), $CSRFSecret = null) 
	{
		$this->node = $node;
		parent::__construct($object, $options, $CSRFSecret);
	}
	
	public function configure()
    {
    	$cuve = $this->getObject()->lots->getOrAdd($this->node);
        $this->embedForm('produits', new DRevLotsProduitsForm($cuve->produits));
        $this->widgetSchema->setNameFormat('drev_lots_produits[%s]');
    }
    
    public function doUpdateObject($values) 
    {
        parent::doUpdateObject($values);
        foreach ($this->getEmbeddedForms() as $key => $embedForm) {
        	$embedForm->doUpdateObject($values[$key]);
        }
    }
}