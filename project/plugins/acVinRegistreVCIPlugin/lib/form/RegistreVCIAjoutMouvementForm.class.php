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

        foreach(ConfigurationClient::getCurrent()->getProduits() as $produit) {
            if($produit->hasRendementVci()){
                $produitLibelle[$produit->getHash()] = $produit->getLibelleComplet();
            }
        }

        foreach($mouvements as $m) {
            $mouvement_type[$m] = RegistreVCIClient::MOUVEMENT_LIBELLE($m);
        }

        $this->setWidgets(array(
            'produit' => new bsWidgetFormChoice(array('choices' => array_merge(['' => ''], $produitLibelle))),
            'lieu' => new sfWidgetFormInput(),
            'mouvement_type' => new bsWidgetFormChoice(array('choices' =>  array_merge(['' => ''], $mouvement_type))),
            'volume' => new bsWidgetFormInputFloat(),
        ));
        
        $this->setValidators(array(
            'produit' =>  new sfValidatorChoice(array('required' => true, 'choices' => array_keys($produitLibelle))),
            'lieu' => new sfValidatorString(array("required" => false)),
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
        if(empty($values['lieu'])) {
            $lieu_id = RegistreVCIClient::LIEU_CAVEPARTICULIERE;
        } else {
            $lieu_id = CompteClient::getInstance()->find($this->values['lieu'])->getEtablissementObj()->getIdentifiant();
        }
        $origine = 'Admin';
        $registreVCI->addLigne($produit, $mouvement_type, $volume, $lieu_id, $origine);
        $registreVCI->save();
    }
}