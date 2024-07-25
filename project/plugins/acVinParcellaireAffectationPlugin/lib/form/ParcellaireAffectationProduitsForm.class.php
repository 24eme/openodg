<?php

class ParcellaireAffectationProduitsForm extends acCouchdbObjectForm {

    protected $cooperative = null;
    protected $etablissement = null;

    public function __construct(acCouchdbJson $object, $cooperative = null, $options = array(), $CSRFSecret = null) {
        $this->cooperative = $cooperative;

        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure() {
		foreach ($this->getParcelles() as $key => $value) {
			$this->embedForm($key, new ParcellaireAffectationProduitAffecteForm($value, $this->getEtablissementAffectation()));
		}

        $this->widgetSchema->setNameFormat('parcelles[%s]');
    }

    public function getEtablissementAffectation() {
        if(!$this->etablissement && $this->cooperative) {
            $this->etablissement = EtablissementClient::getInstance()->find('ETABLISSEMENT-'.explode('-', $this->cooperative)[1]);
        }

        if(!$this->etablissement) {
            $this->etablissement = $this->getObject()->getEtablissementObject();
        }

        return $this->etablissement;
    }

    public function getParcelles() {
        return $this->getObject()->getParcelles();
    }

    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);
        $parcelles = $this->getParcelles();
        $this->getObject()->remove('declaration');
        $this->getObject()->add('declaration');
        foreach ($values as $pid => $items) {
            if (!isset($parcelles[$pid])){
                continue;
            }
            if (!isset($items['affectee']) || !$items['affectee']) {
                continue;
            }
            $parcelle = $parcelles[$pid];
            $node = $this->getObject()->declaration->add(str_replace('/declaration/', '', $parcelle->produit_hash));
            $node->libelle = $node->getConfig()->getLibelleComplet();
            $detail = $node->detail->add($pid, $parcelle);
            $detail->affecter($items['superficie'], $this->getEtablissementAffectation());
        }
    }

}
