<?php

class ParcellaireAffectationChoixDgcForm extends acCouchdbObjectForm {
    
    protected $configuration;

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        $this->configuration = ConfigurationClient::getCurrent();
        parent::__construct($object, $options, $CSRFSecret);
    }
    
    public function configure() {
    	if($this->getObject()->isPapier()) {
    		$this->setWidget('date_papier', new sfWidgetFormInput());
    		$this->setValidator('date_papier', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));
    		$this->getWidget('date_papier')->setLabel("Date de réception du document");
    		$this->getValidator('date_papier')->setMessage("required", "La date de réception du document est requise");
    	}
    	$dgcChoices = $this->getDgcChoices();
   		$this->setWidget('dgc', new sfWidgetFormChoice(array('multiple' => true, 'expanded' => true,'choices' => $dgcChoices)));
   		$this->setValidator('dgc', new sfValidatorChoice(array('multiple' => true, 'required' => true, 'choices' => array_keys($dgcChoices)),array('required' => "Vous devez selectionner vos dénomination complémentaire")));
    	$this->getWidget('dgc')->setLabel("Dénomination complémentaire : ");
        $this->widgetSchema->setNameFormat('choix_dgc[%s]');
    }

    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);
        if (isset($values['dgc'])) {
            foreach ($values['dgc'] as $dgc) {
                foreach (array('rouge', 'rose', 'blanc') as $couleur) {
                    $hash = preg_replace("|/declaration/|", '', $dgc).'/couleurs/'.$couleur.'/cepages/DEFAUT';
                    $commune_dgc = sfConfig::get('app_communes_denominations_'.substr($dgc, -3));

                    $this->getObject()->declaration->setHash($commune_dgc);
                }
                
            }

        }
    }
    
    private function getDgcChoices() {
        $produits = array();
        foreach ($this->configuration->getProduits() as $p) {
            $lieu = $p->getLieu();
            if ($lieu->getKey() == Configuration::DEFAULT_KEY) {
                continue;
            }
            $produits[$lieu->getHash()] = $lieu->libelle;
        }
        return $produits;
    }

}
