<?php
class ConditionnementValidation extends DocumentValidation
{
    const TYPE_ERROR = 'erreur';
    const TYPE_WARNING = 'vigilance';
    const TYPE_ENGAGEMENT = 'engagement';

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
        $this->addControle(self::TYPE_ERROR, 'lot_incomplet', "Les informations du lot sont incomplètes (millésime, numéro de lot, centilisation, produit, volume, spécificité)");
        $this->addControle(self::TYPE_WARNING, 'lot_a_completer', "La date du lot n'a pas été renseignée");
        $this->addControle(self::TYPE_ERROR, 'lot_cepage_volume_different', "Le volume déclaré ne correspond pas à la somme des volumes des cépages");
        /*
         * Engagement
         */
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_ELEVAGE_CONTACT_SYNDICAT, DRevDocuments::getEngagementLibelle(DRevDocuments::DOC_ELEVAGE_CONTACT_SYNDICAT));
    }

    public function controle()
    {
        $this->controleLots();
    }

    protected function controleLots(){
        $produits = [];

      if($this->document->exist('lots')){
        foreach ($this->document->lots as $key => $lot) {

          if($lot->hasBeenEdited()){
            continue;
          }

          if($lot->isEmpty()){
            continue;
          }

          if(!$lot->exist('produit_hash') || !$lot->produit_hash){
            $this->addPoint(self::TYPE_ERROR, 'lot_incomplet', "Lot n° ".($key+1)." : il manque le produit", $this->generateUrl('conditionnement_lots', array("id" => $this->document->_id)));
            continue;
          }
          if(!$lot->exist('volume') || !$lot->volume){
            $this->addPoint(self::TYPE_ERROR, 'lot_incomplet', "Lot n° ".($key+1)." : il manque le volume", $this->generateUrl('conditionnement_lots', array("id" => $this->document->_id)));
            continue;
          }

          if(!$lot->exist('numero_logement_operateur') || !$lot->numero_logement_operateur){
            $this->addPoint(self::TYPE_ERROR, 'lot_incomplet', "Lot n° ".($key+1)." : il manque le numéro de logement", $this->generateUrl('conditionnement_lots', array("id" => $this->document->_id)));
            continue;
          }

          $volume = sprintf("%01.02f",$lot->getVolume());

          if(!$lot->exist('destination_type') || !$lot->destination_type){
              $this->addPoint(self::TYPE_ERROR, 'lot_incomplet', $lot->getProduitLibelle(). " ( ".$volume." hl )", $this->generateUrl('conditionnement_lots', array("id" => $this->document->_id, "appellation" => $key)));
          }
          if(!$lot->exist('specificite') || $lot->specificite == Lot::SPECIFICITE_UNDEFINED){
              $this->addPoint(self::TYPE_ERROR, 'lot_incomplet', $lot->getProduitLibelle(). " ( ".$volume." hl )", $this->generateUrl('conditionnement_lots', array("id" => $this->document->_id, "appellation" => $key)));
              continue;
          }
          if(!$lot->exist('centilisation') || !$lot->centilisation){
              $this->addPoint(self::TYPE_ERROR, 'lot_incomplet', $lot->getProduitLibelle(). " ( ".$volume." hl )", $this->generateUrl('conditionnement_lots', array("id" => $this->document->_id, "appellation" => $key)));
              continue;
          }
          if(!$lot->exist('destination_date') || !$lot->destination_date){
            $this->addPoint(self::TYPE_WARNING, 'lot_a_completer', $lot->getProduitLibelle(). " ( ".$volume." hl )", $this->generateUrl('conditionnement_lots', array("id" => $this->document->_id, "appellation" => $key)));
            continue;
          }


          if(count($lot->cepages)){
            $somme = 0.0;
            foreach ($lot->cepages as $cepage => $v) {
              $somme+=$v;
            }
            if($somme != $lot->volume){
              $this->addPoint(self::TYPE_ERROR, 'lot_cepage_volume_different', $lot->getProduitLibelle(). " ( ".$volume." hl )", $this->generateUrl('conditionnement_lots', array("id" => $this->document->_id, "appellation" => $key)));
            }
          }

          if ($lot->statut == Lot::STATUT_ELEVAGE) {
              $this->addPoint(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_ELEVAGE_CONTACT_SYNDICAT, "$lot->produit_libelle ( $lot->volume hl )");
          }
      }
    }
  }
}
