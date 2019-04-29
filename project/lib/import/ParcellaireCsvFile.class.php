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
     * @throws Exception Si le CVI n'est rattaché à aucun établissement
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
     *
     * @throws Exception Si une parcelle n'est pas conforme
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
                $new_parcelle->superficie_cadastrale = (float) $parcelle[$f::CSV_SUPERFICIE_CADASTRALE];

                $savoir_faire = '';
                if (in_array($parcelle[$f::CSV_FAIRE_VALOIR], ParcellaireClient::$modes_savoirfaire)) {
                    $savoir_faire = array_search($parcelle[$f::CSV_FAIRE_VALOIR], ParcellaireClient::$modes_savoirfaire);
                    $new_parcelle->mode_savoirfaire = $savoir_faire;
                }

                if (! $this->check($new_parcelle)) {
                    throw new Exception("La parcelle ".$new_parcelle->getKey()." n'est pas conforme");
                }
            }
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
        return $parcelle->hasProblemCepageAutorise()
            && $parcelle->hasProblemEcartPieds()
            && $parcelle->hasProblemExpirationCepage()
            && $parcelle->getSuperficie()
            && $parcelle->isAffectee();
    }

    /**
     * Sauve le parcellaire
     */
    public function save()
    {
        $this->parcellaire->save();
    }
}
