<?php

class SV11ApporteursForm extends BaseForm {

    protected $apporteurs = null;
    protected $apporteursSV11 = null;
    protected $sv11 = null;
    protected $coop = null;

    public function __construct($sv11)
    {
        $this->sv11 = $sv11;

        foreach($this->sv11->getEtablissementObject()->getLiaisonOfType(EtablissementClient::TYPE_LIAISON_COOPERATEUR) as $liaison) {
            $this->apporteurs[$liaison->id_etablissement] = $liaison->libelle_etablissement . " - " . $liaison->cvi ;
        }
        $this->apporteursSV11 = $sv11->getApporteurs();
        foreach($this->apporteursSV11 as $idApporteur => $nom) {
            if(isset($this->apporteurs[$idApporteur])) {
                continue;
            }
            $this->apporteurs[$idApporteur] = $nom;
        }
        $defaults = array();
        foreach($this->apporteurs as $id => $apporteur) {
            $defaults[$id] = 1;
        }

        parent::__construct($defaults);
    }

    public function configure() {
        foreach($this->apporteurs as $id => $nom) {
            $this->setWidget($id, new bsWidgetFormInputCheckbox());
            $this->setValidator($id, new ValidatorBoolean());
        }
        $this->widgetSchema->setNameFormat('sv11_apporteurs[%s]');
    }

    public function getApporteurs() {

        return $this->apporteurs;
    }

    public function getApporteursSV11() {

        return $this->apporteursSV11;
    }

    public function save() {
        $coop = $this->sv11->getEtablissementObject();

        foreach($this->getValues() as $id => $check) {
            if(!$check) {
                $apporteur = EtablissementClient::getInstance()->find($id);
                $apporteur->removeLiaison(EtablissementClient::TYPE_LIAISON_COOPERATIVE . '_' . $coop->_id);
                $apporteur->save();
                continue;
            }

            $apporteur = EtablissementClient::getInstance()->find($id);
            $apporteur->addLiaison(EtablissementClient::TYPE_LIAISON_COOPERATIVE, $coop);
            $apporteur->save();
        }
    }
}
