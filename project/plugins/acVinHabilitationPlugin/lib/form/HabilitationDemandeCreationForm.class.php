<?php
class HabilitationDemandeCreationForm extends acCouchdbForm
{
    protected $produits = array();

    public function configure()
    {
        $produits = $this->getProduits();
        $activites = $this->getActivites();
        $statuts = $this->getStatuts();
        $demandes = $this->getDemandes();
        $this->setWidgets(array(
            'produit_hash' => new sfWidgetFormChoice(array('choices' => $produits)),
            'activites' => new sfWidgetFormChoice(array('expanded' => true, 'multiple' => true, 'choices' => $activites)),
            'demande' => new sfWidgetFormChoice(array('choices' => $demandes)),
            'date' => new sfWidgetFormInput(array(), array()),
            'statut' => new sfWidgetFormChoice(array('choices' => $statuts)),
            'commentaire' => new sfWidgetFormInput(array(), array()),
        ));
        $this->widgetSchema->setLabels(array(
            'produit_hash' => 'Produit: ',
            'activites' => 'Activités: ',
            'demande' => 'Demande: ',
            'date' => 'Date: ',
            'statut' => 'Statut: ',
            'commentaire' => 'Commentaire: ',
        ));

        $this->setValidators(array(
            'produit_hash' => new sfValidatorChoice(array('required' => true, 'choices' => array_keys($produits)),array('required' => "Aucun produit saisi.")),
            'activites' => new sfValidatorChoice(array('required' => true, 'multiple' => true, 'choices' => array_keys($activites))),
            'demande' => new sfValidatorChoice(array('required' => true, 'choices' => array_keys($demandes))),
            'date' => new sfValidatorDate(
                array('date_output' => 'Y-m-d',
                'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~',
                'required' => true)),
            'statut' => new sfValidatorChoice(array('required' => true, 'choices' => array_keys($statuts))),
            'commentaire' => new sfValidatorString(array("required" => false)),
        ));

        $this->widgetSchema->setNameFormat('habilitation_demande_creation[%s]');
    }

    public function getProduits()
    {
        if (!$this->produits) {
            foreach ($this->getDocument()->getProduitsConfig() as $produit) {
                if ($this->getDocument()->exist($produit->getHash())) {
                  continue;
                }
                $this->produits[$produit->getHash()] = $produit->getLibelleComplet();
            }
        }
        return array_merge(array('' => ''), $this->produits);
    }

    public function hasProduits()
    {
        return (count($this->getProduits()) > 1);
    }

    public function getActivites(){

        return array_merge( HabilitationClient::$activites_libelles);
    }

    public function getStatuts(){

        return array_merge(array("" => ""), HabilitationClient::$demande_statut_libelles);
    }

    public function getDemandes(){

        return array_merge(array("" => ""), HabilitationClient::$demande_libelles);
    }

    public function save()
    {
        $values = $this->getValues();

        $demande = HabilitationClient::getInstance()->createDemandeAndSave($this->getDocument()->identifiant,
                                                              $values['produit_hash'],
                                                              $values["activites"],
                                                              $values['date'],
                                                              $values['demande'],
                                                              $values['statut'],
                                                              $values['commentaire'], "");

        return $demande;
    }
}
