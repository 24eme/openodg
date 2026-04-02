<?php

class ParcellaireManquantInfosForm extends acCouchdbObjectForm {

    protected $destinataire = null;

    public function __construct(acCouchdbJson $object, $destinataire = null) {
        $this->destinataire = $destinataire;
        parent::__construct($object);
    }

    public function configure() {
		foreach ($this->getObject()->declaration as $key => $value) {
            $this->embedForm($key, new ParcellaireManquantParcellesInfosForm($value, $this->destinataire));
		}

        $this->widgetSchema->setNameFormat('parcelles[%s]');
    }

    protected function doUpdateObject($values) {
    	foreach ($values as $produit => $value) {
    		if (!is_array($value)) continue;
    		foreach ($value as $detail => $items) {
    			$node = $this->getObject()->declaration->get($produit);
    			$node = $node->detail->get($detail);
                if($items['pourcentage'] > 0 && $node->getDefinition()->exist('manquant')) {
                    $node = $node->add('manquant');
                } elseif($node->getDefinition()->exist('manquant')) {
                    $node->remove('manquant');
                }
                if($items['pourcentage'] > 0) {
                    $node->add('pourcentage', $items['pourcentage']);
                    $node->add('densite', $items['densite']);
                }
    		}
    	}
    }

}
