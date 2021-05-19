<?php

/**
 * Description of ExportParcellairePdf
 *
 * @author mathurin
 */
class ExportComptesCsv
{
    protected $compte = null;
    protected $header = false;

    protected $csv;
    protected static $delimiter = ';';

    public static function getHeaderCsv()
    {
        return [
            "Numéro de compte",
            "Intitulé",
            "Type (client/fournisseur)",
            "Abrégé",
            "Adresse",
            "Adresse complément",
            "Code postal",
            "Ville",
            "Pays",
            "Lat",
            "Lon",
            "N° identifiant",
            "N° siret",
            "Statut",
            "Téléphone",
            "Fax",
            "Email",
            "Site"
        ];
    }

    public function __construct($header = true)
    {
        $this->header = $header;
        $this->csv = fopen('php://output', 'w+');
        if ($header) {
            fputcsv($this->csv, self::getHeaderCsv(), self::$delimiter);
        }
    }

    public function getFileName()
    {
        return $this->compte->_id . '_' . $this->compte->_rev . '.csv';
    }

    public function export()
    {
        $compteclient = CompteClient::getInstance();

        foreach (CompteAllView::getInstance()->getAll() as $json_doc) {
            $compte = $compteclient->find($json_doc->id);

            $data = [
                $compte->getCodeComptable(),
                $compte->nom_a_afficher,
                'CLIENT',
                $compte->nom_a_afficher,
                $compte->adresse,
                $compte->adresse_complementaire,
                $compte->code_postal,
                $compte->commune,
                $compte->pays,
                $compte->lat,
                $compte->lon,
                $compte->identifiant,
                $compte->societe_informations->siret,
                $compte->statut,
                ($compte->telephone_bureau) ?: $compte->telephone_mobile,
                $compte->fax,
                $compte->email,
                "https://declaration.syndicat-cotesdurhone.com/societe/$compte->identifiant/visualisation"
            ];

            fputcsv($this->csv, $data, self::$delimiter);
        }
            
        fclose($this->csv);
    }
    /**        
            
            sprintf("%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\n",
            $this->compte->getCodeComptable(),
            $this->compte->nom_a_afficher,
            "CLIENT",
            $this->compte->nom_a_afficher,
            $this->compte->adresse,
            $this->compte->adresse_complementaire,
            $this->compte->code_postal,
            $this->compte->commune,
            $this->compte->pays,
            $this->compte->identifiant,
            $this->compte->societe_informations->siret,
            $this->compte->statut,
            ($this->compte->telephone_bureau) ? $this->compte->telephone_bureau : $this->compte->telephone_mobile,
            $this->compte->fax,
            $this->compte->email,
            "https://declaration.syndicat-cotesdeprovence.com/societe/".$this->compte->identifiant."/visualisation"
        );

    }
     **/
}
