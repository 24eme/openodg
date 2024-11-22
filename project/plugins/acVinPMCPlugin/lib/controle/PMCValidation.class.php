<?php
class PMCValidation extends DocumentValidation
{

    protected $etablissement = null;
    protected $produit_revendication_rendement = array();

    public function __construct($document, $options = null)
    {
        $this->etablissement = $document->getEtablissementObject();
        parent::__construct($document, $options);
        $this->noticeVigilance = true;
    }

    public function configure()
    {
        $this->addControle(self::TYPE_FATAL, 'produits_multi_region', "Les produits d'une même mise en circulation ne doivent pas être géré par 2 syndicats différents");
        $this->addControle(self::TYPE_FATAL, 'lot_incomplet_fatal', "Cette information est incomplète");
        $this->addControle(self::TYPE_FATAL, 'revendication_manquante', "Vous n'avez pas fait votre déclaration de revendication");
        $this->addControle(self::TYPE_ERROR, 'lot_incomplet', "Cette information est incomplète");
        $this->addControle(self::TYPE_ERROR, 'facture_missing', 'Vous n\'avez pas réglé toutes vos factures, vous ne pouvez donc pas valider votre déclaration');
        $this->addControle(self::TYPE_ERROR, 'limite_volume_lot', 'La limite de volume pour un lot est dépassé');
        $this->addControle(self::TYPE_ERROR, 'volume_depasse', "Vous avez dépassé le volume total revendiqué");
        $this->addControle(self::TYPE_WARNING, 'depassement_8515', "Vous devez présenter un papier");
        $this->addControle(self::TYPE_ENGAGEMENT, '8515', "Vous devrez justifier votre assemblage 85/15");
        $this->addControle(self::TYPE_WARNING, 'lot_a_completer', "Cette information pourrait être renseignée");
        $this->addControle(self::TYPE_ERROR, 'date_degust_anterieure', "La date souhaité de dégustation ne peut pas être dans le passé");
        $this->addControle(self::TYPE_WARNING, 'date_degust_proche', "La date souhaité de dégustation est dans moins de 5 semaines et risque de ne pas être validée");
        $this->addControle(self::TYPE_ERROR, 'logement_chai_inexistant', "Vous devez créer le chai logeant le vin");
        $this->addControle(self::TYPE_ERROR, 'logement_chai_secteur_inexistant', "Vous devez affecter un secteur au chai logeant le vin");

        if ($this->document->isNonConformite()) {
            $this->addControle(self::TYPE_ERROR, 'volume_coherent', "Le volume doit rester le même");
        }
    }

    public function controle()
    {
        $this->controleLotsGenerique('pmc_lots');

        if ($this->document->isNonConformite()) {
            $this->controlePMCNC();
        }
    }


    protected function controleLotsGenerique($routeName){
        if(!$this->document->exist('lots')){
            return;
        }

        $totalVolumePMC = [];

        $regions = array();
        foreach ($this->document->lots as $key => $lot) {
            foreach(RegionConfiguration::getInstance()->getOdgRegions() as $region) {
                if(RegionConfiguration::getInstance()->isHashProduitInRegion($region, $lot->produit_hash)) {
                    $regions[$region] = $region;
                }
            }
        }
        if(count($regions) > 1) {
            $this->addPoint(self::TYPE_FATAL, 'produits_multi_region', null, $this->generateUrl($routeName, array("id" => $this->document->_id)));
        }

        if(!$this->document->isNonConformite()) {
            foreach($this->document->getMillesimes() as $millesime) {
                $drev = DRevClient::getInstance()->find(implode('-', ['DREV', $this->document->identifiant, $millesime]));
                if ($drev === null || ! $drev->isValidateOdgByRegion($this->document->region)) {
                    $this->addPoint(self::TYPE_FATAL, 'revendication_manquante', "Déclaration de Revendication ".$millesime, true);
                }
            }
        }
        $campagnes = [];
        foreach ($this->document->lots as $key => $lot) {

            if($lot->isEmpty()){
              continue;
            }
            if ($lot->hasBeenEdited()){
                continue;
            }

            if(!$lot->produit_hash){
              $this->addPoint(self::TYPE_FATAL, 'lot_incomplet_fatal', "Lot n° ".($key+1)." - Produit manquant", $this->generateUrl($routeName, array("id" => $this->document->_id)));
              continue;
            }
            if(!$lot->volume && $lot->volume !== 0){
              $this->addPoint(self::TYPE_FATAL, 'lot_incomplet_fatal', "Lot n° ".($key+1)." - Volume manquant", $this->generateUrl($routeName, array("id" => $this->document->_id)));
              continue;
            }

            $volumeMax = strpos($lot->produit_hash, 'SCR') !== false ? 500 : 1000;
            if ($lot->volume > $volumeMax) {
                $this->addPoint(self::TYPE_ERROR, 'limite_volume_lot', 'Vous ne pouvez pas déclarer plus de '.$volumeMax.' hl de '.$lot->getProduitLibelle().' pour un même lot', $this->generateUrl($routeName, ["id" => $this->document->_id]));
                continue;
            }

            if ($lot->exist('engagement_8515') && $lot->engagement_8515) {
                $this->addPoint(self::TYPE_ENGAGEMENT, '8515', "Lot ".$lot->getProduitLibelle()." ( ".$lot->volume." hl )", $this->generateUrl($routeName, ["id" => $this->document->_id]));
            }

            if (isset($totalVolumePMC[$lot->produit_hash]) === false) { $totalVolumePMC[$lot->produit_hash] = []; }
            if (isset($totalVolumePMC[$lot->produit_hash][$lot->millesime]) === false) { $totalVolumePMC[$lot->produit_hash][$lot->millesime] = 0; }

            if($lot->exist('engagement_8515') && $lot->engagement_8515) {
                $totalVolumePMC[$lot->produit_hash][$lot->millesime] += $lot->volume * 0.85;
            } else {
                $totalVolumePMC[$lot->produit_hash][$lot->millesime] += $lot->volume;
            }
            $campagnes[$lot->millesime."-".($lot->millesime + 1)] = $lot->millesime."-".($lot->millesime + 1);
            $campagnes[$lot->campagne] = $lot->campagne;
            $volume = sprintf("%01.02f",$lot->getVolume());

            if(!$this->document->isValideeOdg() && !$lot->numero_logement_operateur){
              $this->addPoint(self::TYPE_ERROR, 'lot_incomplet', $lot->getProduitLibelle(). " ( ".$volume." hl ) - Numéro de lot", $this->generateUrl($routeName, array("id" => $this->document->_id)));
              continue;
            }
            if(in_array('destination_type', $lot->getFieldsToFill()) && !$lot->destination_type){
                $this->addPoint(self::TYPE_ERROR, 'lot_incomplet', $lot->getProduitLibelle(). " ( ".$volume." hl ) - Type de destination", $this->generateUrl($routeName, array("id" => $this->document->_id, "appellation" => $key)));
                continue;
            }
            if($lot->specificite == Lot::SPECIFICITE_UNDEFINED){
                $this->addPoint(self::TYPE_ERROR, 'lot_incomplet', $lot->getProduitLibelle(). " ( ".$volume." hl ) - Spécificité", $this->generateUrl($routeName, array("id" => $this->document->_id, "appellation" => $key)));
                continue;
            }
            if(!$lot->millesime){
              $this->addPoint(self::TYPE_ERROR, 'lot_incomplet', $lot->getProduitLibelle(). " ( ".$volume." hl ) - Millésime", $this->generateUrl($routeName, array("id" => $this->document->_id, "appellation" => $key)));
              continue;
            }
            if(!$lot->date_degustation_voulue){
                $this->addPoint(self::TYPE_ERROR, 'lot_incomplet', $lot->getProduitLibelle(). " ( ".$volume." hl ) - Date à laquelle le lot peut être prélevé", $this->generateUrl($routeName, array("id" => $this->document->_id, "appellation" => $key)));
                continue;
            }
            if($this->document->isNonConformite()) {
                continue;
            }
            $date_degust = new DateTimeImmutable($lot->date_degustation_voulue);
            $nb_days_from_degust = (int) $date_degust->diff(new DateTimeImmutable($this->document->date))->format('%a');
            if(!$this->document->validation_odg && date('Y-m-d') > $lot->date_degustation_voulue){
              $this->addPoint(self::TYPE_ERROR, 'date_degust_anterieure', $lot->getProduitLibelle(). " ( ".$volume." hl )", $this->generateUrl($routeName, array("id" => $this->document->_id, "appellation" => $key)));
              continue;
            }
            if(!$this->document->isValideeOdg() && $nb_days_from_degust <= 45){
              $this->addPoint(self::TYPE_WARNING, 'date_degust_proche', $lot->getProduitLibelle(). " ( ".$volume." hl ) - Lot prélevable à partir du " . $date_degust->format('d/m/Y') . " ($nb_days_from_degust jours)", $this->generateUrl($routeName, array("id" => $this->document->_id, "appellation" => $key)));
              continue;
            }
        }

        if ($this->document->isNonConformite() === false) {
            $syntheseLots = LotsClient::getInstance()->getSyntheseLots($this->document->identifiant, array_keys($campagnes));
            if(!$this->document->isValideeOdg()) {
                foreach ($totalVolumePMC as $hash => $millesimes) {
                    $produit = ConfigurationClient::getInstance()->getCurrent()->get($hash);
                    foreach ($millesimes as $millesime => $volume) {
                        $volumeDejaCommercialise = @$syntheseLots[$produit->getAppellation()->getLibelle()][$millesime][$produit->getCouleur()->getLibelle()]['PMC'];
                        $volumeDRev = @$syntheseLots[$produit->getAppellation()->getLibelle()][$millesime][$produit->getCouleur()->getLibelle()]['DRev'];
                        if (round($volumeDejaCommercialise + $volume, 2) > round($volumeDRev, 2)) {
                            $this->addPoint(self::TYPE_ERROR, 'volume_depasse', $produit->getLibelleComplet().'  '.$millesime." - ".($volumeDejaCommercialise + $volume)  . " hl déclaré en circulation pour ".$volumeDRev." hl revendiqué" , $this->generateUrl($routeName, array("id" => $this->document->_id)));
                        }
                    }
                }
            }
        }

        if (DRevConfiguration::getInstance()->hasLogementChais() && sfContext::getInstance()->getUser()->isAdmin()) {
            if (!$this->document->chais->nom && !$this->document->chais->adresse && !$this->document->chais->commune && ! $this->document->chais->code_postal) {
                $this->addPoint(self::TYPE_ERROR, 'logement_chai_inexistant', 'Logement', $this->generateUrl('pmc_exploitation', array("id" => $this->document->_id)));
            } elseif(!$this->document->chais->secteur && $this->document->type == PMCClient::TYPE_MODEL) {
                $this->addPoint(self::TYPE_ERROR, 'logement_chai_secteur_inexistant', 'Logement', $this->generateUrl('pmc_exploitation', array("id" => $this->document->_id)));
            }
        }

        if (FactureConfiguration::getInstance()->hasFactureBlockMissing()) {
          $this->factures = FactureClient::getInstance()->getFacturesByCompte($this->document->getEtablissementObject()->getSociete()->identifiant);
          foreach($this->factures as $f) {
            if ($f->hasNonPaiement() && $f->exist('region') && in_array($f->region, $this->document->getRegions())) {
                if ($f->date_facturation > date('Y-m-d', strtotime('-1 year -6 month'))) {
                    $this->addPoint(self::TYPE_ERROR, 'facture_missing', 'Facture '.$f->region.' n°'.$f->numero_archive);
                }
                break;
            }
          }
        }

    }

    protected function controlePMCNC()
    {
        if(!$this->document->exist('lots')){
            return;
        }

        $lotOrigine = $this->document->lots[0];

        if(!$lotOrigine->id_document_provenance) {
            return;
        }

        $docProvenance = DeclarationClient::getInstance()->find($lotOrigine->id_document_provenance);
        $volumeOrigine = $docProvenance->getLot($lotOrigine->unique_id)->volume;

        $volumeTotal = array_reduce($this->document->lots->toArray(), function ($t, $lot) {
            $t += round($lot['volume'], 2);
            return $t;
        }, 0);

        if ($volumeTotal > $volumeOrigine) {
            $this->addPoint(self::TYPE_ERROR, 'volume_coherent', "Le volume revendiqué est de {$volumeTotal} hl alors que l'original est de {$volumeOrigine} hl", $this->generateUrl('pmc_lots', array("id" => $this->document->_id)));
        }
    }
}
