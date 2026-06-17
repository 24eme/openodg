<?php

class ParcellaireIrrigableProduitsForm extends acCouchdbObjectForm {

    protected $destinataire = null;

    public function __construct(acCouchdbJson $object, $destinataire = null) {
        $this->destinataire = $destinataire;
        parent::__construct($object);
    }

    public function configure() {
        foreach ($this->getObject()->getDeclarationParcelles() as $p) {
            if($this->destinataire && !$p->destinations->exist(str_replace("ETABLISSEMENT-", "", $this->destinataire))) {
                continue;
            }
            $value = $p;
            $doc = $this->getObject()->getDocument();
            if(method_exists($doc, 'isDeclarationLiee') && $doc->isDeclarationLiee()) {
                $value = $p->add('irrigation');
            }
            $this->embedForm($p->getParcelleId(), new ParcellaireIrrigableProduitIrrigationForm($value));
        }
        $this->widgetSchema->setNameFormat('parcelles[%s]');
    }

    protected function doUpdateObject($values) {
        $parcelles = $this->getObject()->getDeclarationParcelles();
        foreach ($values as $pid => $value) {
            if (!isset($parcelles[$pid])) {
                continue;
            }
            $node = $parcelles[$pid];

            if($node->getDefinition()->exist('irrigation') && !$value['materiel'] && !$value['ressource']) {
                $node->remove('irrigation');
                continue;
            }

            if($node->getDefinition()->exist('irrigation')) {
                $node = $node->add('irrigation');
            }

            foreach ($value as $k => $v) {
                $node->add($k, $v);
            }
        }
    }
}
