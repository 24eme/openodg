<?php

class ExportControleCSV implements InterfaceDeclarationExportCsv
{
    protected $doc = null;
    protected $header = false;
    private $csv_stream = null;

    public static function getHeaderCsv()
    {
        return "Campagne;Identifiant Société;Identifiant Opérateur;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Email;Type de déclaration;Type tournée;Secteur;Point de contrôle;Manquement Code;Manquement Libelle;Observation;Délais;Date Constat;Date Notification;Cloture Date;Cloture type;Actif;Parcelle concernée;Agent identifiant;Agent Nom;Date;Date Tournée;Heure Tournée;Date de notification;Statut;doc id".PHP_EOL;
    }

    public function __construct($doc, $header = true, $region = null) {
        $this->doc = $doc;
        $this->header = $header;

        $this->csv_stream = fopen('php://output', 'w');
    }

    public function getFileName()
    {
        return $this->doc->_id . '_' . $this->doc->_rev . '.csv';
    }

    public function protectStr($str)
    {
        return str_replace('"', '', $str);
    }

    public function export()
    {
        if ($this->header) {
            fputcsv($this->csv_stream, explode(";", self::getHeaderCsv()), ";");
        }

        $ligne_base = [
            $this->doc->campagne,
            ($this->doc->getEtablissementObject()->getSociete()) ? $this->doc->getEtablissementObject()->getSociete()->identifiant : $this->doc->identifiant,
            $this->doc->identifiant,
            $this->doc->declarant->cvi,
            $this->doc->declarant->siret,
            $this->doc->declarant->raison_sociale,
            $this->doc->declarant->adresse,
            $this->doc->declarant->code_postal,
            $this->doc->declarant->commune,
            $this->doc->declarant->email,
            $this->doc->type,
            $this->doc->type_tournee,
            $this->doc->secteur,
        ];

        $ligne_fin = [
            $this->doc->agent_identifiant,
            CompteClient::getInstance()->find($this->doc->agent_identifiant)->nom_a_afficher,
            $this->doc->date,
            $this->doc->date_tournee,
            $this->doc->heure_tournee,
            $this->doc->notification_date,
            $this->doc->mouvements_statuts[0][2],
            $this->doc->_id,
        ];

        if (count($this->doc->manquements) === 0) {
            $manquement_info = array_fill(0, 10, null);
            $parcelle = null;
            fputcsv($this->csv_stream, array_merge($ligne_base, $manquement_info, [$parcelle], $ligne_fin), ";");
        }

        foreach ($this->doc->manquements as $manquement_code => $manquement) {
            $manquement_info = [
                $manquement->libelle_point_de_controle,
                $manquement_code,
                $manquement->libelle_manquement,
                str_replace(["\r\n", "\n", "\r"], ', ', trim($manquement->observations)),
                $manquement->delais,
                $manquement->constat_date,
                $manquement->notification_date,
                $manquement->cloture_date,
                $manquement->cloture_type,
                $manquement->actif ? "OUI" : "NON",
            ];

            foreach ($manquement->parcelles_id as $parcelle) {
                fputcsv($this->csv_stream, array_merge($ligne_base, $manquement_info, [$parcelle], $ligne_fin), ";");
            }
        }

        fclose($this->csv_stream);
    }

    protected function formatFloat($value)
    {
        return str_replace(".", ",", $value);
    }

    public function setExtraArgs($args) {}
}
