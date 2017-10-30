<?php
class HabilitationAjoutProduitForm extends acCouchdbObjectForm
{
    protected $produits;

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null)
    {
        $this->produits = array();
        parent::__construct($object, $options, $CSRFSecret);
    }

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
            'activites' => new sfValidatorChoice(array('required' => false, 'multiple' => true, 'choices' => array_keys($activites))),
            'statut' => new sfValidatorChoice(array('required' => false, 'choices' => array_keys($statuts))),
            'commentaire' => new sfValidatorString(array("required" => false)),
            'date' => new sfValidatorDate(
                    array('date_output' => 'Y-m-d',
                'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~',
                'required' => false))
        ));

        $this->widgetSchema->setNameFormat('habilitation_ajout_produit[%s]');
    }

    public function getProduits()
    {
        if (!$this->produits) {
            $doc = $this->getObject()->getDocument();
            foreach ($this->getObject()->getConfiguration()->getProduitsCahierDesCharges() as $produit) {
                if ($this->getObject()->exist($produit->getHash())) {
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
      return array_merge(HabilitationClient::$activites_libelles );
    }

    public function getStatuts(){
      return array_merge( array("" => ""), HabilitationClient::$statuts_libelles );
    }



    protected function doUpdateObject($values)
    {
        if (!isset($values['hashref']) || empty($values['hashref'])) {
            return;
        }

        $noeud = $this->getObject()->getDocument()->addProduit($values['hashref']);
        if(!isset($values['statut']) || empty($values['statut']) || !isset($values["activites"]) || !count($values["activites"])){
            return;
        }
        foreach ($noeud->getActivites() as $key => $activite) {
          if(in_array($key,$values["activites"])){
              $activite->updateHabilitation($values['statut'],$values['commentaire'],$values['date']);
          }
        }
    }
}
