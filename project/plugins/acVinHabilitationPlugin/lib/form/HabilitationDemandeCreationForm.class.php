<?php
class HabilitationDemandeCreationForm extends HabilitationDemandeEditionForm
{

    const SITE_PRINCIPAL = 'site_principal';

    public function configure()
    {
        parent::configure();

        $demandes = $this->getDemandes();
        $produits = $this->getProduits();
        $region = Organisme::getCurrentRegion();
        if ($region) {
            foreach($this->getProduits() as $hash_produit => $libelle) {
                if(!RegionConfiguration::getInstance()->isHashProduitInRegion($region, $hash_produit)) {
                    unset($produits[$hash_produit]);
                }
            }
        }
        $activites = $this->getActivites();
        $sites = $this->getSites();

        $this->setWidget('demande', new sfWidgetFormChoice(array('choices' => $demandes)));
        $this->widgetSchema->setLabel('demande', 'Demande: ');
        $this->setValidator('demande',new sfValidatorChoice(array('required' => true, 'choices' => array_keys($demandes))));

        $this->setWidget('produit', new sfWidgetFormChoice(array('choices' => $produits)));
        $this->setWidget('activites', new sfWidgetFormChoice(array('expanded' => true, 'multiple' => true, 'choices' => $activites)));
        $this->setWidget('site', new sfWidgetFormChoice(array('choices' => $sites)));


        $this->widgetSchema->setLabel('produit', 'Produit: ');
        $this->widgetSchema->setLabel('activites', 'Activités: ');
        $this->widgetSchema->setLabel('site', 'Site : ');

        $this->setValidator('produit', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($produits)),array('required' => "Aucun produit saisi.")));
        $this->setValidator('activites', new sfValidatorChoice(array('required' => true, 'multiple' => true, 'choices' => array_keys($activites))));
        $this->setValidator('site', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($sites))));

        $this->widgetSchema->setNameFormat('habilitation_demande_creation[%s]');
    }

    public function getDemandes(){

        return array_merge(array("" => ""), HabilitationClient::getInstance()->getDemandes($this->getOption('filtre')));
    }

    public function getProduits()
    {
        $produits = array();
        foreach ($this->getDocument()->getProduitsConfig(date('Y-m-d')) as $produit) {
            $produits[$produit->getHash()] = preg_replace("/ Tranquilles?$/", '', $produit->getLibelleComplet());
        }
        return array_merge(array('' => ''), $produits);
    }

    public function getActivites(){

        return HabilitationClient::getInstance()->getActivites();
    }

    public function getSites($avec_prefix = true) {
        $sites = array(self::SITE_PRINCIPAL => 'Site principal');
        if ($this->getDocument()->getEtablissementObject()->exist('chais')) {
            foreach($this->getDocument()->getEtablissementObject()->chais as $id => $c) {
                $sites['SITE_'.$id] = ($avec_prefix) ? 'Site secondaire : ' : '';
                if($c->exist('nom') && $c['nom']) {
                    $sites['SITE_'.$id] .= $c['nom'];
                } else {
                    $sites['SITE_'.$id] .= $c['adresse'] . ' ' . $c['code_postal'] . ' ' . $c['commune'];
                }
            }
        }
        return $sites;
    }

    public function save()
    {
        $values = $this->getValues();
        $produits = $this->getProduits();
        $sites = $this->getSites(false);

        if($this->getOption('controle_habilitation')) {
            foreach($values['activites'] as $activite) {
                if($values['demande'] != HabilitationClient::DEMANDE_HABILITATION && (!$this->getDocument()->exist($values['produit'].'/activites/'.$activite) || !$this->getDocument()->get($values['produit'].'/activites/'.$activite)->statut)) {
                    throw new sfException(sprintf("La demande n'a pas pu être créée car l'exploitation n'est pas habilitée en tant que \"%s\" pour le \"%s\"", $activite, $produits[$values['produit']]));
                }
            }
        }
        $site = array();
        if ( isset($values['site']) && ($values['site'] != self::SITE_PRINCIPAL) ) {
            $site[$values['site']] = $sites[$values['site']];
        }
        $demande = HabilitationClient::getInstance()->createDemandeAndSave(
            $this->getDocument()->identifiant,
            $values['demande'],
            $values['produit'],
            $values['activites'],
            $site,
            $values['statut'],
            $values['date'],
            $values['commentaire']
        );

        return $demande;
    }
}
