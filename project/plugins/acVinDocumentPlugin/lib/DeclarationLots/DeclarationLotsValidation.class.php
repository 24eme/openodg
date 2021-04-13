<?php
abstract class DeclarationLotsValidation extends DocumentValidation
{

    public function configureLots()
    {
        $this->addControle(self::TYPE_ERROR, 'lot_incomplet', "Cette information est incomplète");
        $this->addControle(self::TYPE_WARNING, 'lot_a_completer', "Cette information pourrait être renseignée");
        $this->addControle(self::TYPE_ERROR, 'lot_cepage_volume_different', "Le volume déclaré ne correspond pas à la somme des volumes des cépages");
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

            if(!$lot->produit_hash){
              $this->addPoint(self::TYPE_ERROR, 'lot_incomplet', "Lot n° ".($key+1)." - Produit", $this->generateUrl($routeName, array("id" => $this->document->_id)));
              continue;
            }
            if(!$lot->volume){
              $this->addPoint(self::TYPE_ERROR, 'lot_incomplet', "Lot n° ".($key+1)." - Volume", $this->generateUrl($routeName, array("id" => $this->document->_id)));
              continue;
            }

            $volume = sprintf("%01.02f",$lot->getVolume());

            if(!$lot->numero_logement_operateur){
              $this->addPoint(self::TYPE_ERROR, 'lot_incomplet', $lot->getProduitLibelle(). " ( ".$volume." hl ) - Numéro de logement", $this->generateUrl($routeName, array("id" => $this->document->_id)));
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
            if(in_array('centilisation', $lot->getFieldsToFill()) && !$lot->centilisation){
                $this->addPoint(self::TYPE_ERROR, 'lot_incomplet', $lot->getProduitLibelle(). " ( ".$volume." hl ) : Centilisation", $this->generateUrl($routeName, array("id" => $this->document->_id, "appellation" => $key)));
                continue;
            }
            if(!$lot->destination_date){
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
                $somme+=$v;
              }
              if($somme != $lot->volume){
                $this->addPoint(self::TYPE_ERROR, 'lot_cepage_volume_different', $lot->getProduitLibelle(). " ( ".$volume." hl )", $this->generateUrl($routeName, array("id" => $this->document->_id, "appellation" => $key)));
              }
            }

            if ($lot->statut == Lot::STATUT_ELEVAGE) {
                $this->addPoint(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_ELEVAGE_CONTACT_SYNDICAT, "$lot->produit_libelle ( $lot->volume hl )");
            }
        }
    }
}
