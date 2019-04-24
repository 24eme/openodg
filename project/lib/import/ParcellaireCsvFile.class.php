<?php

class ParcellaireCsvFile
{
    /** @var string CSV_TYPE_PARCELLAIRE Le nom du type de CSV */
    const CSV_TYPE_PARCELLAIRE = 'PARCELLAIRE';

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

    /**
     * Constructeur.
     *
     * @param Csv $file Le csv
     */
    public function __construct(Csv $file, ParcellaireCsvFormat $format)
    {
        $this->file = $file;
        $this->format = $format;

        $split = explode('-', pathinfo($file->getFilename(), PATHINFO_FILENAME));
        $this->cvi = $split[1];
        $this->date_maj = $split[2];
        $etablissement = EtablissementClient::getInstance()
                            ->findByCvi($this->cvi);

        if ($etablissement === null) {
            throw new Exception("Le cvi n'est rattaché a aucun établissement");
        }

        $this->etablissement = $etablissement->identifiant;
        $this->parcellaire = ParcellaireClient::getInstance()->findOrCreate(
            $this->etablissement,
            $this->date_maj,
            'PRODOUANE'
        );
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
     */
    public function convert()
    {
        $f = $this->format;
        $configuration = ConfigurationClient::getInstance()->getCurrent();

        foreach ($this->file->getLignes() as $parcelle) {
            if ($parcelle[$f::CSV_PRODUIT] === null) {
                echo "La parcelle n'a pas de produit";
                continue;
            }

            $produit = $configuration->identifyProductByLibelle($parcelle[$f::CSV_PRODUIT]);

            if ($produit) {
                $hash = $produit->getHash();
                $new_parcelle = $this->parcellaire->addParcelle(
                    $hash,
                    $parcelle[$f::CSV_CEPAGE],
                    $parcelle[$f::CSV_CAMPAGNE],
                    $parcelle[$f::CSV_COMMUNE],
                    $parcelle[$f::CSV_SECTION],
                    $parcelle[$f::CSV_NUMERO_PARCELLE],
                    $parcelle[$f::CSV_LIEU_DIT],
                );

                $new_parcelle->ecart_rang = (float) $parcelle[$f::CSV_ECART_RANG];
                $new_parcelle->ecart_pieds = (float) $parcelle[$f::CSV_ECART_PIED];
                $new_parcelle->superficie = (float) $parcelle[$f::CSV_SUPERFICIE];
            }
        }
    }

    /**
     * Sauve le parcellaire
     */
    public function save()
    {
        $this->parcellaire->save();
    }
}
