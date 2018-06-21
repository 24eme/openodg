<?php
class HabilitationDemandeCreationForm extends HabilitationDemandeEditionForm
{
    protected $produits = array();

    public function configure()
    {
        parent::configure();

        $produits = $this->getProduits();
        $activites = $this->getActivites();
        $demandes = $this->getDemandes();

        $this->setWidget('produit_hash', new sfWidgetFormChoice(array('choices' => $produits)));
        $this->setWidget('activites', new sfWidgetFormChoice(array('expanded' => true, 'multiple' => true, 'choices' => $activites)));
        $this->setWidget('demande', new sfWidgetFormChoice(array('choices' => $demandes)));

        $this->widgetSchema->setLabel('produit_hash', 'Produit: ');
        $this->widgetSchema->setLabel('activites', 'Activités: ');
        $this->widgetSchema->setLabel('demande', 'Demande: ');

        $this->setValidator('produit_hash', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($produits)),array('required' => "Aucun produit saisi.")));
        $this->setValidator('activites', new sfValidatorChoice(array('required' => true, 'multiple' => true, 'choices' => array_keys($activites))));
        $this->setValidator('demande',new sfValidatorChoice(array('required' => true, 'choices' => array_keys($demandes))));

        $this->widgetSchema->setNameFormat('habilitation_demande_creation[%s]');
    }

    public function getProduits()
    {
        if (!$this->produits) {
            foreach ($this->getDocument()->getProduitsConfig() as $produit) {
                $this->produits[$produit->getHash()] = $produit->getLibelleComplet();
            }
        }
        return array_merge(array('' => ''), $this->produits);
    }

    public function getActivites(){

        return array_merge(HabilitationClient::$activites_libelles);
    }

    public function getDemandes(){

        return array_merge(array("" => ""), HabilitationClient::$demande_libelles);
    }

    public function save()
    {
        $values = $this->getValues();

        $demande = HabilitationClient::getInstance()->createDemandeAndSave($this->getDocument()->identifiant,
                                                              $values['produit_hash'],
                                                              $values['activites'],
                                                              $values['date'],
                                                              $values['demande'],
                                                              $values['statut'],
                                                              $values['commentaire'], "");

        return $demande;
    }
}
