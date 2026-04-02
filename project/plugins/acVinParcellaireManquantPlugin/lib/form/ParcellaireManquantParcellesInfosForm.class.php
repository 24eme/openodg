<?php

class ParcellaireManquantParcellesInfosForm extends acCouchdbObjectForm {

    protected $destinataire = null;

    public function __construct(acCouchdbJson $object, $destinataire = null) {
        $this->destinataire = $destinataire;
        parent::__construct($object);
    }

    public function configure() {
		foreach ($this->getObject()->detail as $key => $value) {
            if($this->destinataire && !$value->destinations->exist(str_replace("ETABLISSEMENT-", "", $this->destinataire))) {
                continue;
            }
            if($value->exist('manquant')) {
                $value = $value->manquant;
            }
            $this->embedForm($key, new ParcellaireManquantParcelleInfoForm($value));
		}
        $this->widgetSchema->setNameFormat('[%s]');
    }

}
