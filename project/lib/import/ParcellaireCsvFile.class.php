<?php

class ParcellaireCsvFile
{
    /** @var Csv $file Le fichier CSV */
    protected $file;

    /** @var ParcellaireCsvFormat $format Le format du CSV en entrée */
    protected $format;

    /** @var Parcellaire $parcellaire Le parcellaire de sortie*/
    protected $parcellaire;

    /** @var string $cvi Le numéro CVI */
    protected $cvi = '';

    /** @var string $etablissement L'identifiant de l'établissement */
    protected $etablissement = '';

    protected $contextInstance = '';

    public static function getInstance(Parcellaire $parcellaire, $file_path = null, $contextInstance = null) {
        if ($file_path) {
            $c = file_get_contents($file_path);
        }else{
            $c = $parcellaire->getParcellaireCSV();
        }
        if (strpos($c, "Numéro d'exploitation;Raison sociale de l'exploitation;SIRET de l'exploitation;Référence cadastrale") !== false) {
            return new ParcellaireDouaneNatifCsvFile($parcellaire, $file_path, $contextInstance);
        }
        if (strpos($c, "CVI Operateur;Siret Operateur;Nom Operateur;Adresse Operateur;CP Operateur") !== false) {
            return new ParcellaireScrappedCsvFile($parcellaire, $file_path, $contextInstance);
        }
        throw new sfException("Parcellaire CSV non géré ".$parcellaire->_id." ".$file_path);
    }

    /**
     * Constructeur.
     *
     * @param Etablissement $etablissement L'établissement à mettre à jour
     * @param $file Le csv
     *
     * @throws Exception Si le CVI n'est rattaché à aucun établissement
     */
    private function __construct(Parcellaire $parcellaire, $file_path = null, $contextInstance = null)
    {
        $this->parcellaire = $parcellaire;
        $this->etablissement = $parcellaire->getEtablissementObject();
        try {
            $this->contextInstance = ($contextInstance)? $contextInstance : sfContext::getInstance();
        }catch(Exception $e) { }

        if ($file_path)  {
            if ($this->parcellaire->_rev) {
                $this->parcellaire->storeAttachment($file_path, 'text/csv', "import-cadastre-".$this->parcellaire->declarant->cvi."-parcelles.csv");
            }
            $this->file = new Csv($file_path);
        }else{
            $tempfname = tempnam('/tmp', $this->parcellaire->_id);
            $handle = fopen($tempfname, 'w');
            fwrite($handle, $this->parcellaire->getParcellaireCSV());
            fclose($handle);
            $this->file = new Csv($tempfname);
        }

        if ($this->parcellaire->getParcelles()) {

            $this->parcellaire->remove('declaration');
            $this->parcellaire->add('declaration');
            $this->parcellaire->remove('parcelles');
            $this->parcellaire->add('parcelles');
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
    protected function check(ParcellaireParcelle $parcelle)
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
