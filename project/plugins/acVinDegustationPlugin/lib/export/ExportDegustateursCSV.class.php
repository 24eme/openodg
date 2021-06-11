<?php

class ExportDegustateursCSV implements InterfaceDeclarationExportCsv {

    protected $degustation = null;
    protected $header = false;

    public static function getHeaderCsv() {
        return "Campagne;Date;Heure;Num archive;Lieu Dégustation;Adresse lieu;Code postal lieu;Commune lieu;Collège;Dégustateur;Adresse;Code postal;Commune;Date de convocation;Présent;Num table;Organisme;Doc Id;\n";
    }

    public function __construct($degustation, $header = true) {
        $this->degustation = $degustation;
        $this->header = $header;
    }

    public function getFileName() {

        return $this->degustation->_id . '_' . $this->degustation->_rev . '_degustateurs.csv';
    }

    public function export() {
        $csv = "";
        if($this->header) {
            $csv .= self::getHeaderCsv();
        }

        $date = preg_split('/( |T)/', $this->degustation->date, -1, PREG_SPLIT_NO_EMPTY);
        $lieu = explode(' — ', $this->degustation->lieu);
        $organisme = $lieu[0];
        $adresse = null;
        $code_postal = null;
        $commune = null;
        if (preg_match('/^(.+)([0-9]{5})(.+)$/', $lieu[1], $m)) {
          $adresse = trim($m[1]);
          $code_postal = $m[2];
          $commune = trim($m[3]);
        }

        $ligne_base = sprintf("%s;%s;%s;%s;%s;%s;%s;%s",
            $this->degustation->campagne,
            $date[0],
            $date[1],
            $this->degustation->numero_archive,
            $organisme,
            $adresse,
            $code_postal,
            $commune
          );

          foreach($this->degustation->degustateurs as $collegeKey => $comptes) {
              $college = DegustationConfiguration::getInstance()->getLibelleCollege($collegeKey);
              foreach($comptes as $id => $degustateur) {
                $convocation = (isset($degustateur['email_convocation']) && $degustateur['email_convocation'])? $degustateur['email_convocation'] : null;
                $presence = (isset($degustateur['confirmation']) && $degustateur['confirmation'])? 'oui' : 'non';
                $table = (isset($degustateur['numero_table']) && $degustateur['numero_table'])? $degustateur['numero_table'] : null;
                $nom = null;
                $adresse = null;
                $code_postal = null;
                $commune = null;
                if (isset($degustateur['libelle']) && $degustateur['libelle']) {
                  $libelleTab = explode(' — ', preg_replace('/\([A-Z]{2}\)$/', '', $degustateur['libelle']));
                  $nom = $libelleTab[0];
                  if (isset($libelleTab[1]) && $libelleTab[1] && preg_match('/^(.+)([0-9]{5})(.+)$/', $libelleTab[1], $m)) {
                    $adresse = trim($m[1]);
                    $code_postal = $m[2];
                    $commune = trim($m[3]);
                  } elseif (isset($libelleTab[1]) && $libelleTab[1]) {
                    $adresse = trim($m[1]);
                  }
                }
                $organisme = $lieu[0];

                $csv .= sprintf("%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;%s;\n",
                  $ligne_base,
                  $college,
                  $nom,
                  $adresse,
                  $code_postal,
                  $commune,
                  $convocation,
                  $presence,
                  $table,
                  Organisme::getCurrentOrganisme(),
                  $this->degustation->_id
                );
              }
          }
        return $csv;
    }
}
