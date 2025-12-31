<?php
abstract class DeclarationLotsValidation extends DocumentValidation
{
    private $lotselevage = [];

    public function configureLots()
    {
        $this->addControle(self::TYPE_FATAL, 'lot_incomplet_fatal', "Cette information est incomplète");
        $this->addControle(self::TYPE_ERROR, 'lot_incomplet', "Cette information est incomplète");
        $this->addControle(self::TYPE_WARNING, 'lot_a_completer', "Cette information pourrait être renseignée");
        $this->addControle(self::TYPE_FATAL, 'lot_cepage_volume_different', "Le volume déclaré ne correspond pas à la somme des volumes des cépages");
        $this->addControle(self::TYPE_ERROR, 'declaration_habilitation', "Vous n'êtes pas habilité pour cette déclaration");
        /*
         * Engagement
         */
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_ELEVAGE_CONTACT_SYNDICAT, DRevDocuments::getEngagementLibelle(DRevDocuments::DOC_ELEVAGE_CONTACT_SYNDICAT));
    }

    protected function controleLotsGenerique($routeName){
        if(!$this->document->exist('lots')){
            return;
        }

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

            $activite = $this->getActivite($lot->getDocument()->type);
            if (!DRevConfiguration::getInstance()->hasHabilitationINAO() && !$lot->isHabilite($activite)) {
                $this->addPoint(self::TYPE_ERROR, 'declaration_habilitation', $lot->getConfigProduit()->getCepage()->getLibelleComplet(), $this->generateUrl($routeName, array("id" => $this->document->_id)) );
            }

            $volume = sprintf("%01.02f",$lot->getVolume());

            if(!$lot->numero_logement_operateur){
              $this->addPoint(self::TYPE_ERROR, 'lot_incomplet', $lot->getProduitLibelle(). " ( ".$volume." hl ) - Numéro de logement", $this->generateUrl($routeName, array("id" => $this->document->_id)));
              continue;
            }

            if(!$this->document->isValideeOdg() && in_array('destination_type', $lot->getFieldsToFill()) && !$lot->destination_type){
                $this->addPoint(self::TYPE_ERROR, 'lot_incomplet', $lot->getProduitLibelle(). " ( ".$volume." hl ) - Type de destination", $this->generateUrl($routeName, array("id" => $this->document->_id, "appellation" => $key)));
                continue;
            }
            if($lot->specificite == Lot::SPECIFICITE_UNDEFINED){
                $this->addPoint(self::TYPE_ERROR, 'lot_incomplet', $lot->getProduitLibelle(). " ( ".$volume." hl ) - Spécificité", $this->generateUrl($routeName, array("id" => $this->document->_id, "appellation" => $key)));
                continue;
            }
            if(in_array('centilisation', $lot->getFieldsToFill()) && !$lot->centilisation){
                $this->addPoint(self::TYPE_ERROR, 'lot_incomplet', $lot->getProduitLibelle(). " ( ".$volume." hl ) : Centilisation", $this->generateUrl($routeName, array("id" => $this->document->_id, "appellation" => $key)));
                continue;
            }
            if(!$this->document->isValideeOdg() && !$lot->destination_date){
              $this->addPoint(self::TYPE_WARNING, 'lot_a_completer', $lot->getProduitLibelle(). " ( ".$volume." hl ) - Date", $this->generateUrl($routeName, array("id" => $this->document->_id, "appellation" => $key)));
              continue;
            }
            if(!$lot->millesime){
              $this->addPoint(self::TYPE_WARNING, 'lot_a_completer', $lot->getProduitLibelle(). " ( ".$volume." hl ) - Millésime", $this->generateUrl($routeName, array("id" => $this->document->_id, "appellation" => $key)));
              continue;
            }

            if(count($lot->cepages)){
              $somme = 0.0;
              foreach ($lot->cepages as $cepage => $v) {
                  if($v < 0) {
                      continue;
                  }
                $somme+=$v;
              }
              if(round($somme, 2) != round($lot->volume, 2)){
                $this->addPoint(self::TYPE_FATAL, 'lot_cepage_volume_different', $lot->getProduitLibelle(). " ( ".round($lot->volume, 2)." hl vs cépage ".round($somme, 2)." hl )", $this->generateUrl($routeName, array("id" => $this->document->_id, "appellation" => $key)));
              }
            }

            if ($lot->statut == Lot::STATUT_ELEVAGE) {
                $this->lotselevage[$lot->produit_hash][] = [$lot->getProduitLibelle(), $lot->volume];
            }
        }

        if (count($this->lotselevage) > 0) {
            $msg = [];
            foreach ($this->lotselevage as $hash => $lots) {
                $msg[] = count($lots) . " lot(s) de ".$lots[0][0]." (".array_sum(array_column($lots, 1))." hl)";
            }

            $this->addPoint(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_ELEVAGE_CONTACT_SYNDICAT, implode(', ', $msg));
        }
    }

    private function getActivite($type)
    {
        $activite = HabilitationClient::ACTIVITE_VINIFICATEUR;

        switch ($type) {
            case TransactionClient::TYPE_MODEL: $activite = HabilitationClient::ACTIVITE_VRAC; break;
            case ConditionnementClient::TYPE_MODEL: $activite = HabilitationClient::ACTIVITE_CONDITIONNEUR; break;
            case DRevClient::TYPE_MODEL: $activite = HabilitationClient::ACTIVITE_VINIFICATEUR; break;
        }

        $activites = HabilitationClient::getInstance()->getActivites();
        if ($activite == HabilitationClient::ACTIVITE_VRAC && !in_array(HabilitationClient::ACTIVITE_VRAC, array_keys($activites))) {
            return HabilitationClient::ACTIVITE_VINIFICATEUR;
        }
        return $activite;
    }
}
