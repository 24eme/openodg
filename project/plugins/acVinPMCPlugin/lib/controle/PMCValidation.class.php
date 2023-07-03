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
        $this->addControle(self::TYPE_ERROR, 'volume_depasse', "Vous avez dépassé le volume total revendiqué");
        $this->addControle(self::TYPE_WARNING, 'depassement_8515', "Vous devez présenter un papier");
        $this->addControle(self::TYPE_WARNING, '8515', "Vous devrez justifier votre assemblage 85/15");
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

            if ($lot->engagement_8515) {
                $this->addPoint(self::TYPE_WARNING, '8515', "Lot ".$lot->getProduitLibelle()." ( ".$lot->volume." hl )", $this->generateUrl($routeName, ["id" => $this->document->_id]));
            }

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
            $volumeRevendique = $drev->declaration->getTotalVolumeRevendique($hash);

            foreach ($millesimes as $millesime => $volume) {
                $volumeCommercialise = $syntheseLots[$produit->getAppellation()->getLibelle()][$millesime][$produit->getCouleur()->getLibelle()];

                if ($volume + $volumeCommercialise > $volumeRevendique) {
                    if ($lot->engagement_8515 && (($lot->volume * 85 / 100) + $volumeCommercialise) < $volumeRevendique) {
                        $this->addPoint(self::TYPE_WARNING, '8515', "Vous devez présenter un papier");
                    } else {
                      $this->addPoint(self::TYPE_ERROR, 'volume_depasse', "Lot n° ".($key+1)." - Volume dépassé", $this->generateUrl($routeName, array("id" => $this->document->_id)));
                    }
                }
            }
        }

    }

}
