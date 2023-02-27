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

    private $contextInstance = '';

    /**
     * Constructeur.
     *
     * @param Etablissement $etablissement L'établissement à mettre à jour
     * @param Json $file Le Json
     * @param ParcellaireCsvFormat $format Le format du fichier JSON
     *
     * @throws Exception Si le CVI n'est rattaché à aucun établissement
     */
    public function __construct(Etablissement $etablissement, $file, $contextInstance = null)
    {
        $this->etablissement = $etablissement->identifiant;
        $this->file = $file;
        $this->contextInstance = ($contextInstance)? $contextInstance : sfContext::getInstance();

        list(,$this->cvi) = explode('-', pathinfo($file, PATHINFO_FILENAME));
        

        if ($etablissement->cvi !== $this->cvi) {
            $m = sprintf("Les cvi de l'établissement et du nom du fichier ne correspondent pas : %s ≠ %s",
                $etablissement->cvi,
                $this->cvi
            );
            throw new Exception($m);
        }

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
