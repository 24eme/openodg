<?php
class ChgtDenomValidation extends DocumentValidation
{
    const TYPE_ERROR = 'erreur';

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
        $this->addControle(self::TYPE_ERROR, 'lot_volume', "Le volume saisi est supérieur au volume initial.");
        $this->addControle(self::TYPE_ERROR, 'chgtdenom_produit', "Le changement de dénomination n'a pas de produit");
        $this->addControle(self::TYPE_ERROR, 'vip2c_volume_seuil', 'Vous ne pouvez pas changer ce produit car il dépasse le volume seuil');
    }

    public function controle()
    {
        $this->controleLots();

        if (DRevConfiguration::getInstance()->hasVolumeSeuil() && $this->document->campagne === DRevConfiguration::getInstance()->getCampagneVolumeSeuil()) {
            $this->controleVolumeSeuil(DRevConfiguration::getInstance()->getProduitHashWithVolumeSeuil());
        }
    }

    protected function controleLots(){
        $produits = [];

      if($this->document->exist('lots')){
        $lot_origine = $this->getLotDocById_unique();

        foreach ($this->document->lots as $key => $lot) {
          $volume = sprintf("%01.02f",$lot->getVolume());
          $origine_volume = ($lot_origine) ? $lot_origine->volume : $this->document->origine_volume;

          if($lot->volume > $origine_volume){
            $this->addPoint(self::TYPE_ERROR, 'lot_volume', $lot->getProduitLibelle()." $lot->millesime ( ".$volume." hl )", $this->generateUrl('chgtdenom_edition', array("id" => $this->document->_id, "appellation" => $key)));
          }
      }

        if ($this->document->isChgtDenomination() && ! $this->document->changement_produit_hash) {
            $this->addPoint(self::TYPE_ERROR, 'chgtdenom_produit', '');
        }

    }
  }

    public function controleVolumeSeuil($hash)
    {
        if (strpos($this->document->changement_produit_hash, $hash) === false) {
            return false;
        }

        $seuil = $this->document->getVolumeSeuil();
        if ($seuil > 0 && '' > $seuil) {
            $this->addPoint(self::TYPE_ERROR, 'vip2c_volume_seuil', 'Le produit '.$this->document->changement_produit_libelle.' dépasse le volume seuil de '.$seuil.' hl');
        }
    }

  protected function getLotDocById_unique(){
      if (! $this->document->changement_origine_id_document) {
        return null;
      }

    $doc_origine = acCouchdbManager::getClient()->find($this->document->changement_origine_id_document);
    foreach ($doc_origine->lots as $key => $lot_origine) {
      if($this->document->changement_origine_lot_unique_id == $lot_origine->unique_id)
        return $lot_origine;
    }
  }

}
