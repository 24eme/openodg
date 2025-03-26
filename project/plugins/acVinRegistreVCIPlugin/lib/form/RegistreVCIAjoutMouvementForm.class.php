<?php

class RegistreVCIAjoutMouvementForm extends acCouchdbForm 
{
    protected $registre;

    public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null)
    {
        parent::__construct($doc, $defaults, $options, $CSRFSecret);
    }

    public function configure() 
    { 
        $this->registre = $this->getDocument();
        $produitLibelle = [];
        $mouvements = RegistreVCIClient::$mouvement_type;
        $mouvement_type = [];

        foreach($this->registre->getProduits() as $produit) {
            $produitLibelle[$produit->getProduitHash()] = $produit->getLibelle();
        }

        foreach($mouvements as $m) {
            $mouvement_type[$m] = RegistreVCIClient::MOUVEMENT_LIBELLE($m);
        }

        $this->setWidgets(array(
            'produit' => new sfWidgetFormChoice(array('choices' => $produitLibelle)),
            'mouvement_type' => new sfWidgetFormChoice(array('choices' => $mouvement_type)),
            'volume' => new sfWidgetFormInputFloat(),
        ));
        
        $this->setValidators(array(
            'produit' =>  new sfValidatorChoice(array('required' => true, 'choices' => array_keys($produitLibelle))),
            'mouvement_type' => new sfValidatorChoice(array('required' => true, 'choices' => array_keys($mouvement_type))),
            'volume' => new sfValidatorNumber(array('required' => true)),
        ));

        $this->widgetSchema->setNameFormat('vci_ajout_mouvement[%s]');
    }

    public function save() {
        $registreVCI = $this->getDocument();
        $values = $this->getValues();
        $produit = $values['produit'];
        $mouvement_type = $values['mouvement_type'];
        $volume = $values['volume'];
        $lieu_id = RegistreVCIClient::LIEU_CAVEPARTICULIERE;
        $origine = 'Admin';
        $registreVCI->addLigne($produit, $mouvement_type, $volume, $lieu_id, $origine);
        $registreVCI->save();
    }
}