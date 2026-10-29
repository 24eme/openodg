<?php
class PriseDeMousseValidation extends DocumentValidation
{
    const TYPE_ERROR = 'erreur';

    protected $etablissement = null;

    public function __construct($document, $options = null)
    {
        $this->etablissement = $document->getEtablissementObject();
        $lastDrev = DRevClient::getInstance()->findMasterByIdentifiantAndPeriode($document->identifiant, $document->changement_millesime);
        parent::__construct($document, $options);
        $this->noticeVigilance = true;
    }

    public function configure()
    {
        $this->addControle(self::TYPE_ERROR, 'lot_volume_supp', "Le volume saisi est supérieur au volume autorisé.");
        $this->addControle(self::TYPE_ERROR, 'lot_volume_inf', "Le volume saisi ne peut pas être inférieur à zéro.");
        $this->addControle(self::TYPE_ERROR, 'prisedemousse_produit', "Le changement de dénomination n'a pas le bon produit");
    }

    public function controle()
    {
        $this->controleLots();
    }

    protected function controleLots(){
        $produits = [];

        if($this->document->exist('lots')){
            $lot_origine = $this->getLotDocById_unique();

            foreach ($this->document->lots as $key => $lot) {
                $volume = sprintf("%01.02f",$lot->getVolume());
                $origine_volume = ($lot_origine) ? $lot_origine->volume : $this->document->origine_volume;

                if($lot->volume > $origine_volume * 1.2){
                    $this->addPoint(self::TYPE_ERROR, 'lot_volume_supp', $lot->getProduitLibelle()." $lot->millesime ( ".$volume." hl )", $this->generateUrl('prisedemousse_edition', array("id" => $this->document->_id, "appellation" => $key)));
                }

                if ($lot->volume < 0) {
                    $this->addPoint(self::TYPE_ERROR, 'lot_volume_inf', $lot->getProduitLibelle()." $lot->millesime ( ".($origine_volume - $volume)." hl )", $this->generateUrl('prisedemousse_edition', array("id" => $this->document->_id, "appellation" => $key)));
                }
            }

            if (! $this->document->changement_produit_hash || !preg_match('/\/(MOU|VMQ|EFF)\//', $this->document->changement_produit_hash)) {
                $this->addPoint(self::TYPE_ERROR, 'prisedemousse_produit', '');
            }

        }
    }

    protected function getLotDocById_unique(){
        if (! $this->document->changement_origine_id_document) {
            return null;
        }

        $doc_origine = acCouchdbManager::getClient()->find($this->document->changement_origine_id_document);

        if (method_exists($doc_origine, 'getLot') === false) {
            return null;
        }

        foreach ($doc_origine->lots as $key => $lot_origine) {
            if($this->document->changement_origine_lot_unique_id == $lot_origine->unique_id)
            return $lot_origine;
        }
    }

}
