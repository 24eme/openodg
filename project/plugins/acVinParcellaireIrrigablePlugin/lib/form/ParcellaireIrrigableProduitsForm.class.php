<?php

class ParcellaireIrrigableProduitsForm extends acCouchdbObjectForm {

    protected $destinataire = null;

    public function __construct(acCouchdbJson $object, $destinataire = null) {
        $this->destinataire = $destinataire;
        parent::__construct($object);
    }

    public function configure() {
        foreach ($this->getObject()->getParcelles() as $p) {
            if($this->destinataire && !$p->destinations->exist(str_replace("ETABLISSEMENT-", "", $this->destinataire))) {
                continue;
            }
            $this->embedForm($p->getParcelleId(), new ParcellaireIrrigableProduitIrrigationForm((!$p->exist('irrigation')) ? $p : $p->irrigation));
        }
        $this->widgetSchema->setNameFormat('parcelles[%s]');
    }

    protected function doUpdateObject($values) {
        $parcelles = $this->getObject()->getParcelles();
        foreach ($values as $pid => $value) {
            if (!isset($parcelles[$pid])) {
                continue;
            }
            $node = $parcelles[$pid];
            if($node->getDefinition()->exist('irrigation')) {
                $node = $node->add('irrigation');
            }
            foreach ($value as $k => $v) {
                $node->add($k, $v);
            }
        }
    }
}
