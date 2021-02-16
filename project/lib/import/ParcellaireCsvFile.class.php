<?php

class ParcellaireCsvFile
{
    /** @var string CSV_TYPE_PARCELLAIRE Le nom du type de CSV */
    const CSV_TYPE_PARCELLAIRE = 'PARCELLAIRE';
    const CSV_FORMAT_CVI = 0;
    const CSV_FORMAT_SIRET = 1;
    const CSV_FORMAT_NOM = 2;
    const CSV_FORMAT_ADRESSE = 3;
    const CSV_FORMAT_CP = 4;
    const CSV_FORMAT_COMMUNE_OP = 5;
    const CSV_FORMAT_EMAIL = 6;
    const CSV_FORMAT_IDU = 7;
    const CSV_FORMAT_COMMUNE = 8;
    const CSV_FORMAT_LIEU_DIT = 9;
    const CSV_FORMAT_SECTION = 10;
    const CSV_FORMAT_NUMERO_PARCELLE = 11;
    const CSV_FORMAT_PRODUIT = 12;
    const CSV_FORMAT_CEPAGE = 13;
    const CSV_FORMAT_SUPERFICIE = 14;
    const CSV_FORMAT_SUPERFICIE_CADASTRALE = 15;
    const CSV_FORMAT_CAMPAGNE = 16;
    const CSV_FORMAT_ECART_PIED = 17;
    const CSV_FORMAT_ECART_RANG = 18;
    const CSV_FORMAT_FAIRE_VALOIR = 19;
    const CSV_FORMAT_STATUT = 20;
    const CSV_FORMAT_DATE_MAJ = 21;

    /** @var Csv $file Le fichier CSV */
    private $file;

    /** @var ParcellaireCsvFormat $format Le format du CSV en entrée */
    private $format;

    /** @var Parcellaire $parcellaire Le parcellaire de sortie*/
    private $parcellaire;

    /** @var string $cvi Le numéro CVI */
    private $cvi = '';

    /** @var string $etablissement L'identifiant de l'établissement */
    private $etablissement = '';

    /** @var string $date_maj La date de mise à jour */
    private $date_maj = '';

    private $contextInstance = '';

    /**
     * Constructeur.
     *
     * @param Etablissement $etablissement L'établissement à mettre à jour
     * @param Csv $file Le csv
     * @param ParcellaireCsvFormat $format Le format du fichier CSV
     *
     * @throws Exception Si le CVI n'est rattaché à aucun établissement
     */
    public function __construct(Etablissement $etablissement, Csv $file, $contextInstance = null)
    {
        $this->etablissement = $etablissement->identifiant;
        $this->file = $file;
        $this->contextInstance = ($contextInstance)? $contextInstance : sfContext::getInstance();

        list(,$this->cvi) = explode('-', pathinfo($file->getFilename(), PATHINFO_FILENAME));


        if ($etablissement->cvi !== $this->cvi) {
            $m = sprintf("Les cvi de l'établissement et du fichier ne correspondent pas : %s ≠ %s",
                $etablissement->cvi,
                $this->cvi
            );
            throw new Exception($m);
        }

        $this->parcellaire = ParcellaireClient::getInstance()->findOrCreate(
            $this->etablissement,
            date('Y-m-d'),
            'PRODOUANE'
        );
        
        if ($this->parcellaire->getParcelles()) {

            $this->parcellaire->remove('declaration');
            $this->parcellaire->add('declaration');
        }
    }

    /**
     * Retourne le fichier CSV
     *
     * @return Csv
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Retourne le CVI
     *
     * @return string
     */
    public function getCvi()
    {
        return $this->cvi;
    }

    /**
     * Retourne la date de mise à jour
     *
     * @return string
     */
    public function getDateMaj()
    {
        return $this->date_maj;
    }

    /**
     * Retourne l'identifiant de l'établissement
     *
     * @return string
     */
    public function getEtablissement()
    {
        return $this->etablissement;
    }

    /**
     * Retourne le parcellaire
     *
     * @return Parcellaire
     */
    public function getParcellaire()
    {
        return $this->parcellaire;
    }

    /**
     * Analyse le CSV et le transforme en parcellaire
     *
     * @throws Exception Si une parcelle n'est pas conforme
     */
    public function convert()
    {
        $configuration = ConfigurationClient::getInstance()->getCurrent();

        foreach ($this->file->getLignes() as $parcelle) {

            if (!is_numeric($parcelle[self::CSV_FORMAT_CVI]) && !is_numeric($parcelle[self::CSV_FORMAT_SIRET]) && !is_numeric($parcelle[self::CSV_FORMAT_CP]) && !is_numeric($parcelle[self::CSV_FORMAT_IDU]) && !is_numeric($parcelle[self::CSV_FORMAT_SUPERFICIE])) {
                continue;
            }

            if ($parcelle[self::CSV_FORMAT_PRODUIT] === null) {
                $this->contextInstance->getLogger()->info("Parcelle sans produit : ".implode(',', $parcelle));
                continue;
            }

            $produit = $configuration->identifyProductByLibelle($parcelle[self::CSV_FORMAT_PRODUIT]);

            if (!$produit && ParcellaireConfiguration::getInstance()->getLimitProduitsConfiguration()) {
                $this->contextInstance->getLogger()->info("ParcellaireCsvFile : produit non reconnu : ".$parcelle[self::CSV_FORMAT_PRODUIT] );
                continue;
            }
            $hash = ($produit) ? $produit->getHash() : null ;
            $new_parcelle = $this->parcellaire->addParcelle(
                $hash,
                $parcelle[self::CSV_FORMAT_CEPAGE],
                $parcelle[self::CSV_FORMAT_CAMPAGNE],
                $parcelle[self::CSV_FORMAT_COMMUNE],
                $parcelle[self::CSV_FORMAT_SECTION],
                $parcelle[self::CSV_FORMAT_NUMERO_PARCELLE],
                $parcelle[self::CSV_FORMAT_LIEU_DIT]
            );

            $new_parcelle->ecart_rang = (float) $parcelle[self::CSV_FORMAT_ECART_RANG];
            $new_parcelle->ecart_pieds = (float) $parcelle[self::CSV_FORMAT_ECART_PIED];
            $new_parcelle->superficie = (float) $parcelle[self::CSV_FORMAT_SUPERFICIE];
            $new_parcelle->superficie_cadastrale = (float) $parcelle[self::CSV_FORMAT_SUPERFICIE_CADASTRALE];
            $new_parcelle->set('mode_savoirfaire',$parcelle[self::CSV_FORMAT_FAIRE_VALOIR]);

            if (! $this->check($new_parcelle)) {
                $this->contextInstance->getLogger()->info("La parcelle ".$new_parcelle->getKey()." n'est pas conforme");
                throw new Exception("La parcelle ".$new_parcelle->getKey()." n'est pas conforme");
            }
            $this->contextInstance->getLogger()->info("Parcelle de ".$new_parcelle->getKey()." ajouté");
        }
    }

    /**
     * Vérifie une parcelle
     *
     * @param ParcellaireParcelle $parcelle La parcelle à vérifier
     * @return bool
     */
    private function check(ParcellaireParcelle $parcelle)
    {

        return $parcelle->getSuperficie();
    }

    /**
     * Sauve le parcellaire
     */
    public function save()
    {
        $this->parcellaire->save();
    }
}
