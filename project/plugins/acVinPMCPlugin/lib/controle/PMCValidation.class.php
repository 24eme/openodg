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
        $this->addControle(self::TYPE_FATAL, 'lot_incomplet_fatal', "Cette information est incomplète");
        $this->addControle(self::TYPE_ERROR, 'lot_incomplet', "Cette information est incomplète");
        $this->addControle(self::TYPE_ERROR, 'limite_volume_lot', 'La limite de volume pour un lot est dépassé');
        $this->addControle(self::TYPE_ERROR, 'volume_depasse', "Vous avez dépassé le volume total revendiqué");
        $this->addControle(self::TYPE_WARNING, 'depassement_8515', "Vous devez présenter un papier");
        $this->addControle(self::TYPE_ENGAGEMENT, '8515', "Vous devrez justifier votre assemblage 85/15");
        $this->addControle(self::TYPE_WARNING, 'lot_a_completer', "Cette information pourrait être renseignée");
        $this->addControle(self::TYPE_WARNING, 'date_degust_proche', "La date est dans moins de 5 semaines et risque de ne pas être validée");
    }

    public function controle()
    {
        $this->controleLotsGenerique('pmc_lots');
    }


    protected function controleLotsGenerique($routeName){
        if(!$this->document->exist('lots')){
            return;
        }

        $totalVolumePMC = [];

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
                $this->addPoint(self::TYPE_ERROR, 'limite_volume_lot', 'Vous ne pouvez pas revendiquer plus de '.$volumeMax.' hl de '.$lot->getProduitLibelle(), $this->generateUrl($routeName, ["id" => $this->document->_id]));
                continue;
            }

            if ($lot->exist('engagement_8515') && $lot->engagement_8515) {
                $this->addPoint(self::TYPE_ENGAGEMENT, '8515', "Lot ".$lot->getProduitLibelle()." ( ".$lot->volume." hl )", $this->generateUrl($routeName, ["id" => $this->document->_id]));
            }

            if (isset($totalVolumePMC[$lot->produit_hash]) === false) { $totalVolumePMC[$lot->produit_hash] = []; }
            if (isset($totalVolumePMC[$lot->produit_hash][$lot->millesime]) === false) { $totalVolumePMC[$lot->produit_hash][$lot->millesime] = 0; }
            $totalVolumePMC[$lot->produit_hash][$lot->millesime] += $lot->volume;

            $volume = sprintf("%01.02f",$lot->getVolume());

            if(!$lot->numero_logement_operateur){
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
                $this->addPoint(self::TYPE_ERROR, 'lot_incomplet', $lot->getProduitLibelle(). " ( ".$volume." hl ) - Date de dégustation", $this->generateUrl($routeName, array("id" => $this->document->_id, "appellation" => $key)));
                continue;
            }
            $date_degust = new DateTimeImmutable($lot->date_degustation_voulue);
            $nb_days_from_degust = (int) $date_degust->diff(new DateTimeImmutable($this->document->date))->format('%a');
            if($nb_days_from_degust <= 45){
              $this->addPoint(self::TYPE_WARNING, 'date_degust_proche', $lot->getProduitLibelle(). " ( ".$volume." hl ) - Date de dégustation souhaitée (" . $date_degust->format('d/m/Y') . ")", $this->generateUrl($routeName, array("id" => $this->document->_id, "appellation" => $key)));
              continue;
            }
        }

        $syntheseLots = LotsClient::getInstance()->getSyntheseLots($this->document->identifiant, $this->document->campagne);
        $drev = DRevClient::getInstance()->find(implode('-', ['DREV', $this->document->identifiant, substr($this->document->campagne, 0, 4)]));

        foreach ($totalVolumePMC as $hash => $millesimes) {
            $produit = ConfigurationClient::getInstance()->getCurrent()->get($hash);
            $volumeRevendique = ($drev) ? $drev->declaration->getTotalVolumeRevendique($hash) : 0;

            foreach ($millesimes as $millesime => $volume) {
                if (isset($syntheseLots[$produit->getAppellation()->getLibelle()]) === false) { $volumeCommercialise = 0; }
                elseif (isset($syntheseLots[$produit->getAppellation()->getLibelle()][$millesime]) === false) { $volumeCommercialise = 0; }
                elseif (isset($syntheseLots[$produit->getAppellation()->getLibelle()][$millesime][$produit->getCouleur()->getLibelle()]) === false) { $volumeCommercialise = 0; }
                else {
                    $volumeCommercialise = $syntheseLots[$produit->getAppellation()->getLibelle()][$millesime][$produit->getCouleur()->getLibelle()];
                }

                if ($volume + $volumeCommercialise > $volumeRevendique) {
                    if ($lot->exist('engagement_8515') && $lot->engagement_8515 && (($lot->volume * 85 / 100) + $volumeCommercialise) < $volumeRevendique) {
                        $this->addPoint(self::TYPE_ENGAGEMENT, '8515', "Vous devez présenter un papier");
                    } else {
                      $this->addPoint(self::TYPE_ERROR, 'volume_depasse', "Lot n° ".($key+1)." - Volume dépassé", $this->generateUrl($routeName, array("id" => $this->document->_id)));
                    }
                }
            }
        }

    }

}
