<?php
class HabilitationDemandeCreationIdentificationForm extends HabilitationDemandeCreationFormAbstract
{
    public function configure()
    {
        parent::configure();

        $this->embedForm('donnees', new HabilitationDemandeDonneesIdentificationForm($this->getDocument()));
    }

    public function getDemandes(){
        $demandes = array();
        foreach(HabilitationClient::$demandes_declarant as $demande) {
            $demandes[$demande] = HabilitationClient::$demande_libelles[$demande];
        }
        return $demandes;
    }

    public function getDonnees() {
        $donnees = array();

        foreach(parent::getDonnees() as $key => $value) {
            if(!$value || $this->getDocument()->declarant->get($key) == $value) {
                continue;
            }
            
            $donnees[$key] = $value;
        }

        return $donnees;
    }

}
