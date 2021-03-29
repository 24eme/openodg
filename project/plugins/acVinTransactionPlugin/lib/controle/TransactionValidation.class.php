<?php
class TransactionValidation extends DocumentValidation
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
        $this->addControle(self::TYPE_ERROR, 'lot_produit_non_saisi', "Aucun produit n'a été saisi");
        $this->addControle(self::TYPE_ERROR, 'lot_volume_non_saisi', "Aucun volume n'a été saisi");
        $this->addControle(self::TYPE_ERROR, 'lot_millesime_non_saisie', "Le millesime du lot n'a pas été saisie");
        $this->addControle(self::TYPE_ERROR, 'lot_destination_type_non_saisie', "La destination du lot n'a pas été renseignée");
        $this->addControle(self::TYPE_WARNING, 'lot_destination_date_non_saisie', "La date du lot n'a pas été renseignée");
        $this->addControle(self::TYPE_ERROR, 'lot_cepage_volume_different', "Le volume déclaré ne correspond pas à la somme des volumes des cépages");
        /*
         * Engagement
         */
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_ELEVAGE_CONTACT_SYNDICAT, DRevDocuments::getEngagementLibelle(DRevDocuments::DOC_ELEVAGE_CONTACT_SYNDICAT));
    }

    public function controle()
    {
        $this->controleProduits();
        $this->controleLots();
    }

    protected function controleProduits(){
        $produits = [];
        foreach ($this->document->lots as $key => $lot) {
          if((!$lot->exist('produit_hash') || !$lot->produit_hash) && (!$lot->exist('volume') || !$lot->volume)){
            continue;
          }
          if(!$lot->exist('produit_hash') || !$lot->produit_hash){
            $this->addPoint(self::TYPE_ERROR, 'lot_produit_non_saisi', "Lot n° ".($key+1), $this->generateUrl('transaction_lots', array("id" => $this->document->_id)));
          }
          if(!$lot->exist('volume') || !$lot->volume){
            $this->addPoint(self::TYPE_ERROR, 'lot_volume_non_saisi', "Lot n° ".($key+1), $this->generateUrl('transaction_lots', array("id" => $this->document->_id)));
          }
        }
    }

    protected function controleLots(){
        $produits = [];

      if($this->document->exist('lots')){
        foreach ($this->document->lots as $key => $lot) {
          if($lot->hasBeenEdited()){
            continue;
          }
          if(!$lot->hasVolumeAndHashProduit()){
            continue;
          }
          $volume = sprintf("%01.02f",$lot->getVolume());
          if(!$lot->exist('destination_type') || !$lot->destination_type){
              $this->addPoint(self::TYPE_ERROR, 'lot_destination_type_non_saisie', $lot->getProduitLibelle(). " ( ".$volume." hl )", $this->generateUrl('transaction_lots', array("id" => $this->document->_id, "appellation" => $key)));
          }
          if(!$lot->exist('destination_date') || !$lot->destination_date){
            $this->addPoint(self::TYPE_WARNING, 'lot_destination_date_non_saisie', $lot->getProduitLibelle(). " ( ".$volume." hl )", $this->generateUrl('transaction_lots', array("id" => $this->document->_id, "appellation" => $key)));
          }


          if(count($lot->cepages)){
            $somme = 0.0;
            foreach ($lot->cepages as $cepage => $v) {
              $somme+=$v;
            }
            if($somme != $lot->volume){
              $this->addPoint(self::TYPE_ERROR, 'lot_cepage_volume_different', $lot->getProduitLibelle(). " ( ".$volume." hl )", $this->generateUrl('transaction_lots', array("id" => $this->document->_id, "appellation" => $key)));
            }
          }

          if ($lot->statut == Lot::STATUT_ELEVAGE) {
              $this->addPoint(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_ELEVAGE_CONTACT_SYNDICAT, "$lot->produit_libelle ( $lot->volume hl )");
          }
      }
    }
  }
}
