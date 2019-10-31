<?php

class ParcellaireJsonFile
{
    /** @var string JSON_TYPE_PARCELLAIRE Le nom du type de CSV */
    const JSON_TYPE_PARCELLAIRE = 'PARCELLAIRE';

    /** @var Json $file Le fichier JSON */
    private $file;

    /** @var ParcellaireCsvFormat $format Le format du JSON en entrée */
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
     * @param Etablissement $etablissement L'établissement à mettre à jour
     * @param Json $file Le Json
     * @param ParcellaireCsvFormat $format Le format du fichier JSON
     *
     * @throws Exception Si le CVI n'est rattaché à aucun établissement
     */
    public function __construct(Etablissement $etablissement, $file)
    {
        $this->etablissement = $etablissement->identifiant;
        $this->file = $file;

        [,$this->cvi] = explode('-', pathinfo($file, PATHINFO_FILENAME));
        

        if ($etablissement->cvi !== $this->cvi) {
            $m = sprintf("Les cvi de l'établissement et du fichier ne correspondent pas : %s ≠ %s",
                $etablissement->cvi,
                $this->cvi
            );
            throw new Exception($m);
        }
        //print_r($this->cvi);
        //print_r("\n");
        //print_r($this->etablissement);
        //exit;
        $this->parcellaire = ParcellaireClient::getInstance()->findOrCreateDocJson(
            $this->etablissement,
            date('Y-m-d'),
            'PRODOUANE',
            $file,
            $this->cvi
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
                    $parcelle[$f::CSV_LIEU_DIT]
                );

                $new_parcelle->ecart_rang = (float) $parcelle[$f::CSV_ECART_RANG];
                $new_parcelle->ecart_pieds = (float) $parcelle[$f::CSV_ECART_PIED];
                $new_parcelle->superficie = (float) $parcelle[$f::CSV_SUPERFICIE];
                $new_parcelle->superficie_cadastrale = (float) $parcelle[$f::CSV_SUPERFICIE_CADASTRALE];
                $new_parcelle->set('mode_savoirfaire',$parcelle[$f::CSV_FAIRE_VALOIR]);

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
