<?php

class FichierClient extends acCouchdbClient {

    const CATEGORIE_DR = "Dr";
    const CATEGORIE_SV11 = "Sv11";
    const CATEGORIE_SV12 = "Sv12";
    const CATEGORIE_DREV = "Drev";
    const CATEGORIE_IDENTIFICATION = "Identification";
    const CATEGORIE_DEGUSTATION = "Degustation";
    const CATEGORIE_FACTURE = "Facture";
    const CATEGORIE_CHGTDENOM = "ChgtDenom";
    const CATEGORIE_OI = "OI";
    const CATEGORIE_FICHIER = 'fichier';

    public static function getInstance()
    {
      return acCouchdbManager::getClient("Fichier");
    }

    public function createDoc($identifiant, $papier = false)
    {
        $fichier = new Fichier();
        $fichier->initDoc($identifiant);

        if($papier) {
            $fichier->add('papier', 1);
        }

        $fichier->date_import = date('Y-m-d');

        return $fichier;
    }

    /**
     * Scrape le site des douanes pour récupérer des documents administratifs
     * et les converti en document CouchDB
     *
     * @param Etablissement $etablissement Un objet CouchDB Etablissement
     * @param string $type Le type de document à scraper
     * @param string $annee L'année de création du document
     *
     * @return false|Un document
     */
    public function scrapeAndSaveFiles($etablissement, $type, $annee, $scrap = true, $context = null)
    {
        $etablissements = $etablissement->getMeAndLiaisonOfType(EtablissementClient::TYPE_LIAISON_METAYER);
        $fichiers = array();
        foreach($etablissements as $etblmt) {
            if($scrap) {
                $this->scrapeFiles($etblmt, $type, $annee);
            }
            if (!$files = $this->getScrapyFiles($etblmt, strtolower($type), $annee, false, $context)) {
                continue;
            }
            $client = $this->getClientFromType($type);
            if (!$fichier = $client->findByArgs($etblmt->identifiant,  $annee)) {
                $fichier = $client->createDoc($etblmt->identifiant, $annee);
            }
            if ($fichier->isNew()) {
                $fichier->setLibelle("$type $annee issue de Prodouane");
                $fichier->save();
            }
            try {
                foreach ($files as $file) {
                    $fichier->storeFichier($file);
                    unlink($file);
                }
                $fichier->save();
                //On convertit l'exception en quelque chose de traitable par sf
            } catch (Exception $e) {
                throw new sfException($e->getMessage());
                return;
            }
            $fichiers[] = $fichier;
        }
        return $fichiers;
    }

    private function scrapeFiles($etablissement, $type, $annee)
    {
        $types = array(
            DRCsvFile::CSV_TYPE_DR,
            SV11CsvFile::CSV_TYPE_SV11,
            SV12CsvFile::CSV_TYPE_SV12,
        );

        if (!in_array($type, $types)) {
            throw new sfException("$type is not allowed for scrapy file");
        }

        try {
            return ProdouaneScrappyClient::scrape($type, $annee, $etablissement->cvi);
        }catch($e) {
        }

        $scrapydocs = ProdouaneScrappyClient::getDocumentPath();

        if (!preg_match('/^[0-9]{4}$/', $annee)) {
            throw new sfException("$annee is not a valid year for scrapy file");
        }

        if (!$etablissement->cvi || !preg_match('/^[0-9A]{5}[0-9A-Z]{5}$/i', $etablissement->cvi)) {
            throw new sfException("CVI : ".$etablissement->cvi." is not a valid cvi for scrapy file");
        }

        $t = strtolower($type);
        $cvi = $etablissement->cvi;

        $files = $this->getScrapyFiles($etablissement, $t, $annee, true);

        $status = ProdouaneScrappyClient::exec("download_douane.sh", "$t $annee $cvi 1>&2", $output);
    }

    public function getScrapyFiles($etablissement, $type, $annee, $listonly = false, $context = null)
    {
        try {
            if ($listonly) {
                return ProdouaneScrappyClient::list($type, $annee, $etablissement->cvi);
            }
            return ProdouaneScrappyClient::listAndSaveInTmp($type, $annee, $etablissement->cvi);
        }catch($e) {
        }
        $files = array();
        $directory = new DirectoryIterator(ProdouaneScrappyClient::getDocumentPath($context));
        $iterator = new IteratorIterator($directory);
        if ($annee > 2021 && ($type == 'sv11' || $type == 'sv12')) {
            $type = 'production';
        }
        $regex = new RegexIterator($directory, '/^'.$type.'-'.$annee.'-'.$etablissement->cvi.'\..+$/i', RegexIterator::MATCH);
        foreach($regex as $file) {
            $file_path = $file->getPathname);
            if(!is_writable($file_path) {
                throw new sfException("File ".$file." not writable. Once the new version has been downloaded, it cannot be replaced");
            }
            $files[] = $file_path;
        }
        return $files;
    }

    public function findByArgs($type, $identifiant, $annee)
    {

        return $this->getClientFromType($type)->findByArgs($identifiant, $annee);
    }

    /**
    * Retourne une instance d'un client en fonction du type
    *
    * @param string $type Le type de document
    *
    * @return Un client
    */
    public function getClientFromType($type)
    {
        switch ($type) {
            case 'DR':
            $client = DRClient::getInstance();
            break;
            case 'SV11':
            $client = SV11Client::getInstance();
            break;
            case 'SV12':
            $client = SV12Client::getInstance();
            break;
            default:
            $client = null;
        }
        return $client;
    }

    public function getCategories() {

        return array(
            self::CATEGORIE_DR => "DR",
            self::CATEGORIE_SV11 => "SV11",
            self::CATEGORIE_SV12 => "SV12",
            self::CATEGORIE_DREV => "DRev",
            self::CATEGORIE_IDENTIFICATION => "Habilitation / Identification",
            self::CATEGORIE_DEGUSTATION => "Dégustation",
            self::CATEGORIE_FACTURE => "Facture",
            self::CATEGORIE_CHGTDENOM => "Changement de dénomination",
            self::CATEGORIE_OI => "OI",
        );
    }
}
