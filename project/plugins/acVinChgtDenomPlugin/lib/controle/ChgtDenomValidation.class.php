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

    }

    public function controle()
    {
        $this->controleLots();
    }

    protected function controleLots(){
        $produits = [];

      if($this->document->exist('lots')){
        foreach ($this->document->lots as $key => $lot) {
          $lot_origine = $this->getLotDocById_unique();
          $volume = sprintf("%01.02f",$lot->getVolume());
          if($lot->volume > $lot_origine->volume){
            $this->addPoint(self::TYPE_ERROR, 'lot_volume', $lot->getProduitLibelle()." $lot->millesime ( ".$volume." hl )", $this->generateUrl('chgtdenom_edition', array("id" => $this->document->_id, "appellation" => $key)));
          }
      }
    }
  }

  protected function getLotDocById_unique(){
    $doc_origine = acCouchdbManager::getClient()->find($this->document->changement_origine_id_document);
    foreach ($doc_origine->lots as $key => $lot_origine) {
      if($this->document->changement_origine_lot_unique_id == $lot_origine->unique_id)
        return $lot_origine;
    }
  }

}
