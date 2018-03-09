<?php
/**
 * Model for EtablissementLiaisonsOperateurs
 *
 */

class EtablissementLiaisonsOperateurs extends BaseEtablissementLiaisonsOperateurs {

    public function getChai(){
        if(!$this->aliases->exist('chai') || !$this->aliases->chai){
            return null;
        }
        $etblie = EtablissementClient::getInstance()->find($this->id_etablissement);
        $chaiNom = $this->aliases->chai;
        foreach ($etblie->getChais() as $c) {
            if($c->nom == $chaiNom){
                return $c;
            }
        }
        return null;
    }
}
