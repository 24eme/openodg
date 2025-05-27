<?php

class ParcellaireAffectationProduitsForm extends acCouchdbObjectForm {

    protected $destinataire = null;
    protected $hashproduitFilter = null;

    public function __construct(acCouchdbJson $object, $destinataire, $hashproduitFilter, $options = array(), $CSRFSecret = null) {
        $this->destinataire = EtablissementClient::getInstance()->find($destinataire);
        $this->hashproduitFilter = $hashproduitFilter;
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure() {
		foreach ($this->getParcelles() as $key => $value) {
			$this->embedForm($key, new ParcellaireAffectationProduitAffecteForm($value, $this->destinataire));
		}

        $this->widgetSchema->setNameFormat('parcelles[%s]');
    }

    public function getParcelles() {
        return $this->getObject()->getParcelles($this->hashproduitFilter);
    }

    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);
        $parcelles = $this->getParcelles();
        foreach($parcelles as $parcelle) {
            $parcelle->desaffecter($this->destinataire);
        }
        $this->getObject()->remove('declaration');
        $this->getObject()->add('declaration');
        foreach ($parcelles as $pid => $parcelle) {
            $items = $values[$pid];
            if (!isset($parcelles[$pid])){
                continue;
            }
            if (isset($values[$pid]['affectee']) && $values[$pid]['affectee']) {
                $parcelle->affecter($items['superficie'], $this->destinataire);
            }
            if(!$parcelle->isAffectee()) {
                continue;
            }
            $this->getObject()->addParcelle($parcelle);
        }
    }

}
