<?php

class exportEtablissementsCsvTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'export';
        $this->name = 'etablissements-csv';
        $this->briefDescription = "Export csv des établissements";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $results = EtablissementClient::getInstance()->findAll();

        foreach($results->rows as $row) {
            $etablissement = EtablissementClient::getInstance()->find($row->id, acCouchdbClient::HYDRATE_JSON);
            $societe = SocieteClient::getInstance()->find($etablissement->id_societe, acCouchdbClient::HYDRATE_JSON);
            $habilitation = HabilitationClient::getInstance()->getLastHabilitation($etablissement->identifiant, acCouchdbClient::HYDRATE_JSON);

            $habilitationStatut = null;
            $activites = array();
            if(isset($habilitation)) {
                foreach($habilitation->declaration as $produit) {
                    foreach($produit->activites as $activiteKey => $activite) {
                        if(!$activite->statut) {
                            continue;
                        }
                        $activites[] = $activiteKey;
                        $habilitationStatut = $activite->statut;
                    }
                }
            }

            $ordre = null;

            if($etablissement->region && $etablissement->famille == EtablissementFamilles::FAMILLE_PRODUCTEUR) {
                $ordre = 'CP ';
            }
            if($etablissement->region && $etablissement->famille == EtablissementFamilles::FAMILLE_COOPERATIVE) {
                $ordre = 'CC ';
            }
            if($etablissement->region && $etablissement->famille == EtablissementFamilles::FAMILLE_NEGOCIANT) {
                $ordre = 'N';
            }
            if($etablissement->region) {
                $ordre .= substr($etablissement->code_postal, 0, 2);
            }

            echo
            $societe->identifiant.";".
            $etablissement->famille.";".
            "".";". // INTITULE
            $etablissement->raison_sociale.";".
            "\"".$etablissement->adresse."\";".
            "\"".$etablissement->adresse_complementaire."\";".
            $etablissement->code_postal.";".
            $etablissement->commune.";".
            $etablissement->cvi.";".
            $etablissement->siret.";".
            $etablissement->telephone_bureau.";".
            $etablissement->telephone_mobile.";".
            $etablissement->telephone_perso.";".
            $etablissement->fax.";".
            $etablissement->email.";".
            implode("|", $activites).";". // Activité habilitation
            $habilitationStatut.";". // Statut habilitation
            $ordre.";". // Ordre
            $etablissement->region.";".
            $societe->code_comptable_client.";".
            $etablissement->statut.";".
            str_replace("\n", '\n', $etablissement->commentaire).";".
            "\n";

        }
    }
}
