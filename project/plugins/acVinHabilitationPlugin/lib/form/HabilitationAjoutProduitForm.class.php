<?php
class HabilitationAjoutProduitForm extends acCouchdbForm
{
    protected $produits = array();

    public function configure()
    {
        $produits = $this->getProduits();
        $activites = $this->getActivites();
        $statuts = $this->getStatuts();
        $this->setWidgets(array(
            'hashref' => new sfWidgetFormChoice(array('choices' => $produits)),
            'activites' => new sfWidgetFormChoice(array('expanded' => true, 'multiple' => true, 'choices' => $activites)),
            'statut' => new sfWidgetFormChoice(array('choices' => $statuts)),
            'commentaire' => new sfWidgetFormChoice(array('choices' => $statuts)),
            'date' => new sfWidgetFormInput(array(), array())
        ));
        $this->widgetSchema->setLabels(array(
            'hashref' => 'Produit: ',
            'activites' => 'Activités: ',
            'statut' => 'Statut: ',
            'commentaire' => 'Commentaire: ',
            'date' => 'Date: ',
        ));

        $this->setValidators(array(
            'hashref' => new sfValidatorChoice(array('required' => true, 'choices' => array_keys($produits)),array('required' => "Aucun produit saisi.")),
            'activites' => new sfValidatorChoice(array('required' => true, 'multiple' => true, 'choices' => array_keys($activites))),
            'date' => new sfValidatorDate(
                    array('date_output' => 'Y-m-d',
                          'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~',
                          'required' => true)),
            'statut' => new sfValidatorChoice(array('required' => true, 'choices' => array_keys($statuts))),
            'commentaire' => new sfValidatorString(array("required" => false)),
        ));

        $this->widgetSchema->setNameFormat('habilitation_ajout_produit[%s]');
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
      return array_merge(array("" => ""), HabilitationClient::$statuts_libelles);
    }

    public function save()
    {
        $values = $this->getValues();

        HabilitationClient::getInstance()->updateAndSaveHabilitation($this->getDocument()->identifiant,
                                                              $values['hashref'],
                                                              $values['date'],
                                                              $values["activites"],
                                                              $values['statut'],
                                                              $values['commentaire']);
    }
}
