<?php
class HabilitationDemandeGlobaleForm extends HabilitationDemandeCreationForm
{
    public function configure()
    {
        parent::configure();

        unset($this->widgetSchema['produit']);
        unset($this->validatorSchema['produit']);
        unset($this->widgetSchema['activites']);
        unset($this->validatorSchema['activites']);
        unset($this->widgetSchema['site']);
        unset($this->validatorSchema['site']);

        $this->widgetSchema->setNameFormat('habilitation_demande_globale[%s]');
    }

    public function save() {
        $demandes = array();
        $values = $this->getValues();

        $produits = $this->getDocument()->getProduitsHabilites();
        foreach($produits as $produit) {
            $activites = array_keys($produit->getActivitesHabilites());

            $demande = HabilitationClient::getInstance()->createDemandeAndSave(
                $this->getDocument()->identifiant,
                $values['demande'],
                $produit->getHash(),
                array_keys($produit->getActivitesHabilites()),
                null,
                $values['statut'],
                $values['date'],
                $values['commentaire'],
                null
            );
            $demandes[] = $demande;
        }

        return $demandes;
    }

}
