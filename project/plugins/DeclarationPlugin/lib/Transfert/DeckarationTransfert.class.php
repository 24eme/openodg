<?php

class DeclarationTransfert
{
    protected $identifiantFrom;
    protected $identifiantTo;

    public function __construct($identifiantFrom, $identifiantTo) {
        $this->identifiantFrom = $identifiantFrom;
        $this->identifiantTo = $identifiantTo;

        if(!EtablissementClient::getInstance()->findByIdentifiant($this->identifiantFrom)) {

            throw new sfException(sprintf("L'établisssement ETABLISSEMENT-%s n'existe pas", $this->identifiantFrom));
        }

        if(!EtablissementClient::getInstance()->findByIdentifiant($this->identifiantTo)) {

            throw new sfException(sprintf("L'établisssement ETABLISSEMENT-%s n'existe pas", $this->identifiantTo));
        }
    }

    public function transfert() {
        $idsDoc = DeclarationClient::getInstance()->getIdsByIdentifiant($this->identifiantFrom);

        $idsTransferes = array();

        foreach($idsDoc  as $idFrom) {
            $idTo = str_replace($this->identifiantFrom, $this->identifiantTo, $idFrom);

            if(DeclarationClient::getInstance()->find($idTo, acCouchdbClient::HYDRATE_JSON)) {
                continue;
            }

            $ls = new LS();
            $ls->set('_id', $idTo);
            $ls->setPointeur($idFrom);
            $ls->save();

            $idsTransferes[$idFrom] = $idTo;
        }

        return $idsTransferes;
    }


}
