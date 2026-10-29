<?php

class DRaPProduitsForm extends acCouchdbObjectForm {

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
            $this->embedForm($p->getParcelleId(), new DRaPProduitDestinationsForm((!$p->exist('destinations')) ? $p : $p->destinations));
        }
        $this->widgetSchema->setNameFormat('parcelles[%s]');
    }

    protected function doUpdateObject($values) {
        $parcelles = $this->getObject()->getParcelles();
        foreach ($parcelles as $parcelle) {
            $pid = $parcelle->getParcelleId();
            if (! isset($values[$pid])) {
                continue;
            }

            $value = $values[$pid];

            foreach ($value as $k => $v) {
                $parcelle->add($k, $v);
            }
        }
    }
}
