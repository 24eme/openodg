<?php 

class ExportReserveInterproCSV {
    protected $header = false;


    public static function getHeaderCsv() {
        return "Campagne;Identifiant;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Email;Type de ligne;Certification;Certification Libelle;Genre;Genre Libelle;Appellation;Appellation Libelle;Lieu;Lieu Libelle;Couleur;Couleur Libelle;Cepage;Cepage Libelle;INAO;Dénomination complémentaire;Produit;Superficie revendiquée;Volume mis en reserve;Volume revendiqué commercialisable\n";
    }
    
    public function __construct($header = true) {
        $this->header = $header;
    }

    public function getFileName() {
        return 'reserve-interpro.csv';
    }
    
    public function protectStr($str) {
        return str_replace(';', '−', str_replace('"', '', $str));
    }


    public function export() {
        $csv = "";
        if($this->header) {
            $csv .= self::getHeaderCsv();
        }

        foreach ([DRevClient::TYPE_MODEL] as $type) {
                foreach(DeclarationExportView::getInstance()->getDeclarations($type, "2024-2025")->rows as $json_doc){
                    $drev = DeclarationClient::getInstance()->find($json_doc->id);
                    if($drev instanceof DRev && !$drev->isMaster()){
                        continue;
                    }
                    $csv .= $this->getCsvLinesByDRev($drev);

                }
          return $csv;
        }
    }
    
    public function exportForOneDRev($drev) {
        $csv = "";
        if($this->header) {
            $csv .= self::getHeaderCsv();
        }
        $csv .= $this->getCsvLinesByDRev($drev);
        return $csv;
    }

    public function getCsvLinesByDRev($drev) {
        $csv = "";
        "Campagne;Identifiant;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Email;Certification;Certification Libelle;Genre;Genre Libelle;Appellation;Appellation Libelle;Lieu;Lieu Libelle;Couleur;Couleur Libelle;Cepage;Cepage Libelle;INAO;Dénomination complémentaire;Produit;Superficie revendiquée;Volume mis en reserve;Volume revendiqué commercialisable\n";

        $ligneBase = sprintf("%s;%s;%s;%s;%s;%s;%s;%s;%s;",
        $drev->campagne,
        $drev->identifiant,
        $drev->declarant->cvi,
        $drev->declarant->siret,
        $this->protectStr($drev->declarant->raison_sociale),
        $this->protectStr($drev->declarant->adresse),
        $drev->declarant->code_postal,
        $this->protectStr($drev->declarant->commune),
        $drev->declarant->email);

        foreach($drev->getProduitsWithReserveInterpro($this->region) as $produit){
            $configProduit = $produit->getConfig();
            $certification = $configProduit->getCertification()->getKey();
            $certificationLibelle = $configProduit->getCertification()->getLibelle();
            $genre = $configProduit->getGenre()->getKey();
            $genreLibelle =  $configProduit->getGenre()->getLibelle();
            $appellation = $configProduit->getAppellation()->getKey();
            $appellationLibelle = $configProduit->getAppellation()->getLibelle();
            $lieu = $configProduit->getLieu()->getKey();
            $lieuLibelle = $configProduit->getLieu()->getLibelle();
            $couleur = $configProduit->getCouleur()->getKey();
            $couleurLibelle = $configProduit->getCouleur()->getLibelle();
            $cepage = $configProduit->getCepage()->getKey();
            $cepageLibelle = $configProduit->getCepage()->getLibelle();
            $inao = $configProduit->getCodeDouane();
            $denominationComplementaire = $produit->denomination_complementaire;

            $csv .= $ligneBase;
            
            $csv .= sprintf(";%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\n",
                $certification,$certificationLibelle,$genre,$genreLibelle,$appellation,$appellationLibelle,$lieu,$lieuLibelle,$couleur,$couleurLibelle,$cepage,$cepageLibelle,$inao,$denominationComplementaire,
                $libelle_complet = $produit->getLibelleComplet(),
                $produit->superficie_revendique,
                $produit->getVolumeReserveInterpro(),
                $produit->getVolumeRevendiqueCommecialisable(),
                );
        }
        return $csv;
    }
}