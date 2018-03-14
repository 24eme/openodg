<?php
/**
 * Model for EtablissementLiaisonsOperateurs
 *
 */

class EtablissementLiaisonsOperateurs extends BaseEtablissementLiaisonsOperateurs {

    public function getChai(){
        if($this->hash_chai && $this->id_etablissement){
            $etblie = EtablissementClient::getInstance()->find($this->id_etablissement);
            $chaihash = $this->hash_chai;
            if($etblie && $etblie->$chaihash){
                return $etblie->$chaihash;
            }
        }

        return null;
    }
}
