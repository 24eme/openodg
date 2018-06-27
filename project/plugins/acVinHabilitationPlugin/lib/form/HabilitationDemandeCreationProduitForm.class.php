<?php
class HabilitationDemandeCreationProduitForm extends HabilitationDemandeCreationFormAbstract
{
    public function configure()
    {
        parent::configure();

        $this->embedForm('donnees', new HabilitationDemandeDonneesProduitForm($this->getDocument()));
    }

    public function getDemandes(){
        $demandes = array("" => "");
        foreach(HabilitationClient::$demandes_produit as $demande) {
            $demandes[$demande] = HabilitationClient::$demande_libelles[$demande];
        }
        return $demandes;
    }

}
