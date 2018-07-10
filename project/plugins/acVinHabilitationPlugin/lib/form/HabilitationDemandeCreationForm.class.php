<?php
class HabilitationDemandeCreationForm extends HabilitationDemandeEditionForm
{
    public function configure()
    {
        parent::configure();

        $demandes = $this->getDemandes();
        $produits = $this->getProduits();
        $activites = $this->getActivites();

        $this->setWidget('demande', new sfWidgetFormChoice(array('choices' => $demandes)));
        $this->widgetSchema->setLabel('demande', 'Demande: ');
        $this->setValidator('demande',new sfValidatorChoice(array('required' => true, 'choices' => array_keys($demandes))));

        $this->setWidget('produit', new sfWidgetFormChoice(array('choices' => $produits)));
        $this->setWidget('activites', new sfWidgetFormChoice(array('expanded' => true, 'multiple' => true, 'choices' => $activites)));

        $this->widgetSchema->setLabel('produit', 'Produit: ');
        $this->widgetSchema->setLabel('activites', 'Activités: ');

        $this->setValidator('produit', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($produits)),array('required' => "Aucun produit saisi.")));
        $this->setValidator('activites', new sfValidatorChoice(array('required' => true, 'multiple' => true, 'choices' => array_keys($activites))));

        $this->widgetSchema->setNameFormat('habilitation_demande_creation[%s]');
    }

    public function getDemandes(){

        return array_merge(array("" => ""), HabilitationClient::$demande_libelles);
    }

    public function getProduits()
    {
        $produits = array();
        foreach ($this->getDocument()->getProduitsConfig() as $produit) {
            $produits[$produit->getHash()] = $produit->getLibelleComplet();
        }
        return array_merge(array('' => ''), $produits);
    }

    public function getActivites(){

        return array_merge(HabilitationClient::$activites_libelles);
    }

    public function save()
    {
        $values = $this->getValues();

        $demande = HabilitationClient::getInstance()->createDemandeAndSave(
            $this->getDocument()->identifiant,
            $values['demande'],
            $values['produit'],
            $values['activites'],
            $values['statut'],
            $values['date'],
            $values['commentaire'],
            ""
        );

        return $demande;
    }
}
