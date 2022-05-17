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

        return array_merge(array("" => ""), HabilitationClient::getInstance()->getDemandes($this->getOption('filtre')));
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

        return HabilitationClient::getInstance()->getActivites();
    }

    public function save()
    {
        $values = $this->getValues();
        $produits = $this->getProduits();

        if($this->getOption('controle_habilitation')) {
            foreach($values['activites'] as $activite) {
                if($values['demande'] != HabilitationClient::DEMANDE_HABILITATION && !$this->getDocument()->isHabiliteFor($values['produit'], $activite)) {
                    throw new sfException(sprintf("La demande n'a pas pu être créée car l'exploitation n'est pas habilitée en tant que \"%s\" pour le \"%s\"", $activite, $produits[$values['produit']]));
                }
            }
        }

        $chais_id = null;
        if ($this->getDocument()->exist('chais_id')) {
            $chais_id = $this->getDocument()->chais_id;
        }

        $demande = HabilitationClient::getInstance()->createDemandeAndSave(
            $this->getDocument()->getEtablissementIdentifiant(),
            $this->getDocument()->getChaisId(),
            $values['demande'],
            $values['produit'],
            $values['activites'],
            $values['statut'],
            $values['date'],
            $values['commentaire'],
            null
        );

        return $demande;
    }

    public function getEtablissementChais() {
        return $this->getDocument()->getEtablissementChais();
    }
}
