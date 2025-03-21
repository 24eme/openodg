<?php

class RegistreVCIAjoutMouvementForm extends acCouchdbForm 
{
    protected $produits;
    protected $date;
    protected $lieu;
    protected $mouvement_type;

    // $registre_VCI, $produit, $date, $lieu, $mouvement_type
    public function __construct(\acCouchdbJson $object, $options = array(), $CSRFSecret = null) 
    {
        parent::__construct($object, $options, $CSRFSecret);
    }


    public function configure() 
    { 
        $registreVCI = $this->doc;
        $produitLibelle = [];
        $produitLieu = [];

        foreach($registreVCI->getProduits() as $produit) {
            $produitLibelle[] = $produit->getLibelle();
            
            foreach($produit->getDetails() as $detail) {
                $produitLieu[] = $detail->getStockageLibelle();
            }            
        }
        

        $this->setWidgets(array(
            'produit' => new sfWidgetFormChoice(array('choices' => $produitLibelle)),
            'date' => new bsWidgetFormInputDate(array(), array()),
            'lieu' => new sfWidgetFormChoice(array('choices' => $produitLieu)),
            // 'mouvement_type' => new sfWidgetFormChoice(array('choices' => $produits)),
            
        ));
        
          $this->setValidator('produit', new sfValidatorChoice(array('choices' => array_keys($registreVCI->getProduits()))));

    }
}