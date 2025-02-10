<?php
class ChgtDenomValidation extends DocumentValidation
{
    const TYPE_ERROR = 'erreur';

    protected $etablissement = null;
    protected $produit_revendication_rendement = array();
    protected $contrats = [];
    protected $vip2c = null;

    public function __construct($document, $options = null)
    {
        $this->etablissement = $document->getEtablissementObject();
        $lastDrev = DRevClient::getInstance()->findMasterByIdentifiantAndPeriode($document->identifiant, $document->changement_millesime);
        if($lastDrev) {
            $this->vip2c = VIP2C::gatherInformations($lastDrev, $lastDrev->getPeriode());
        } else {
            $this->vip2c = ['produits' => []];
        }
        parent::__construct($document, $options);
        $this->noticeVigilance = true;
    }

    public function configure()
    {
        $this->addControle(self::TYPE_ERROR, 'lot_volume', "Le volume saisi est supérieur au volume initial.");
        $this->addControle(self::TYPE_ERROR, 'chgtdenom_produit', "Le changement de dénomination n'a pas de produit");
        if($this->document->changement_produit_hash) {
            $this->addControle(self::TYPE_ERROR, 'vip2c_pas_de_contrats', "Depuis le millésime ".VIP2C::getConfigCampagneVolumeSeuil().", la filière a mis en place le Volume Individuel de Production Commercialisable Certifiée (VIP2C). Vous avez dépassé les  ".$this->document->getVolumeSeuil($this->vip2c['produits'])." hl de ".$this->document->getConfigProduitChangement()->getLibelleComplet()." qui vous ont été attribués. Pour pouvoir revendiquer ces lots, vous devez apporter une preuve de leur commercialisation or Declarvins nous informe que vous n'avez pas de contrat de vrac non soldé. Veuillez prendre contact avec Intervins Sud Est - 04 90 42 90 04.");
            $this->addControle(self::TYPE_WARNING, 'vip2c_volume_seuil', 'Pour le millésime 2022, la filière a mis en place le Volume Individuel de Production Commercialisable Certifiée (<strong>VIP2C</strong>) sur le '.$this->document->getConfigProduitChangement()->getLibelleComplet().'. Vous dépassez le seuil qui vous a été attribué, vous devrez avoir une preuve de commercialisation');
        }
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_VIP2C_OU_CONTRAT_VENTE_EN_VRAC, DRevDocuments::getEngagementLibelle(DRevDocuments::DOC_VIP2C_OU_CONTRAT_VENTE_EN_VRAC));
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_VIP2C_OU_PAS_INFORMATION, "<strong>Je n'ai pas l'information</strong>");
        $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_VIP2C_OU_CONDITIONNEMENT, "<strong>J'atteste de conditionnements,</strong> en revendiquant au-delà de mon Volume Individuel de Production Commercialisable Certifiée (VIP2C), je m'engage à fournir à Intervins Sud Est <strong>une copie du registre de conditionnement</strong>.");

        if (VIP2C::hasVolumeSeuil() && $this->document->campagne >= VIP2C::getConfigCampagneVolumeSeuil()) {
            $this->contrats = VIP2C::getContratsFromAPI($this->document->declarant->cvi, $this->document->changement_millesime, $this->document->changement_produit_hash);

            if($this->contrats){
                foreach($this->contrats as $contrat_id => $contrat_info){
                    $this->addControle(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_VIP2C_OU_CONTRAT_VENTE_EN_VRAC."_".$contrat_id, DRevDocuments::getEngagementLibelle(DRevDocuments::DOC_VIP2C_OU_CONTRAT_VENTE_EN_VRAC).'<strong>'.$contrat_info['numero']."</strong> avec un volume proposé de <strong>".$contrat_info['volume']." hl</strong>.");
                }
            }
        }

        if ($this->document->isFromProduction()) {
            $this->addControle(self::TYPE_ERROR, 'production_volume_max', "Le volume changé dépasse le volume restant du document de production");
            $this->addControle(self::TYPE_ERROR, 'production_volume_mismatch', "Les volumes ne correspondent pas");
            $this->addControle(self::TYPE_ERROR, 'not_in_production', "Produit non présent dans le document de production");
            $this->addControle(self::TYPE_ERROR, 'production_no_L15', "Produit sans L15");
            $this->addControle(self::TYPE_ERROR, 'not_in_drev', "Produit non présent dans la revendication");
        }
    }

    public function controle()
    {
        $this->controleLots();

        if (VIP2C::hasVolumeSeuil() && $this->document->campagne >= VIP2C::getConfigCampagneVolumeSeuil()) {
            $this->controleVolumeSeuil();
        }

        if ($this->document->isFromProduction()) {
            $this->controleProduction();
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

          if ($lot->volume < 0) {
              $this->addPoint(self::TYPE_ERROR, 'lot_volume', $lot->getProduitLibelle()." $lot->millesime ( ".($origine_volume - $volume)." hl )", $this->generateUrl('chgtdenom_edition', array("id" => $this->document->_id, "appellation" => $key)));
          }
      }

        if ($this->document->isChgtDenomination() && ! $this->document->changement_produit_hash) {
            $this->addPoint(self::TYPE_ERROR, 'chgtdenom_produit', '');
        }

    }
  }

    public function controleProduction()
    {
        if ($this->document->origine_volume !== $this->document->changement_volume) {
            $this->addPoint(self::TYPE_ERROR, 'production_volume_mismatch', "Volume origine (".$this->document->origine_volume." hl) n'est pas égal au volume changé (".$this->document->changement_volume." hl)");
        }

        $doc = DeclarationClient::getInstance()->find($this->document->changement_origine_id_document);
        $produits = $doc->getProduits();
        $hash = str_replace('/declaration/', '', $this->document->origine_produit_hash);

        if (array_key_exists($hash, $produits) === false) {
            $this->addPoint(self::TYPE_ERROR, 'not_in_production', "Vous n'avez pas récolté de ".$this->document->origine_produit_libelle);
            return;
        }

        if (array_key_exists(15, $produits[$hash]["lignes"]) === false) {
            $this->addPoint(self::TYPE_ERROR, 'production_no_L15', $this->document->origine_produit_libelle);
        }

        $campagne = substr($this->document->campagne, 0, 4);
        $lastDrev = DRevClient::getInstance()->findMasterByIdentifiantAndPeriode($this->document->identifiant, $campagne);
        $synthese = $lastDrev->summerizeProduitsLotsByCouleur();

        if (array_key_exists($this->document->origine_produit_libelle." ".$campagne, $synthese) === false) {
            $this->addPoint(self::TYPE_ERROR, 'not_in_drev', "Vous n'avez pas revendiqué de " . $this->document->origine_produit_libelle);
            return;
        }
        $maxVolume = $synthese[$this->document->origine_produit_libelle." ".$campagne]["volume_restant_max"];

        if ($maxVolume < $this->document->origine_volume) {
            $this->addPoint(self::TYPE_ERROR, 'production_volume_max', "Le volume changé (".$this->document->origine_volume." hl) dépasse le volume max (".$maxVolume." hl)");
        }
    }

    public function controleVolumeSeuil()
    {
        $seuil = $this->document->getVolumeSeuil($this->vip2c['produits']);
        if (!$seuil) {
            return;
        }

        $synthese = LotsClient::getInstance()->getSyntheseLots($this->document->identifiant, array($this->document->campagne));
        preg_match('/([\w ]+) (Rouge|Rosé|Blanc)/u', $this->document->changement_produit_libelle, $matches);
        $volume_produit = $synthese[$matches[1]][$this->document->changement_millesime][$matches[2]]['Lot'];
        if ($seuil > 0 && ($volume_produit + $this->document->changement_volume) > $seuil) {
            if(sfContext::getInstance()->getUser()->hasChgtDenomAdmin()) {
                $this->addPoint(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_VIP2C_OU_PAS_INFORMATION,"");
                $this->addPoint(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_VIP2C_OU_CONDITIONNEMENT, "");
            }

            if (! $this->contrats) {
                $this->addPoint(self::TYPE_ERROR, 'vip2c_pas_de_contrats', null, $this->generateUrl('chgtdenom_edition', array("id" => $this->document->_id)) );
                return false;
            }

            $this->addPoint(self::TYPE_WARNING, 'vip2c_volume_seuil', 'Vous avez déjà commercialisé <strong>'.($volume_produit + $this->document->changement_volume).'</strong> hl sur votre seuil attribué de <strong>'.$seuil.'</strong> hl');

            foreach ($this->contrats as $contrat_id => $contrat_info) {
                $this->addPoint(self::TYPE_ENGAGEMENT, DRevDocuments::DOC_VIP2C_OU_CONTRAT_VENTE_EN_VRAC."_".$contrat_id, "");
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
