<?php

class exportHabilitationDemandesCsvTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'export';
        $this->name = 'habilitation-historique';
        $this->briefDescription = "Export de l'historique des habilitations";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $rows = HabilitationHistoriqueView::getInstance()->getAll();

        echo "Identifiant;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Adresse complémentaire 1;Adresse complémentaire 2;Code postal Opérateur;Commune Opérateur;Email;Téléphone Bureau;Téléphone Mobile;Demande;Libellé activités;Produit;Statut;Date Statut;Statut précédent;Date précédent statut;Statut suivant;Date statut suivant;Id du doc;Clé de la demande";

        foreach ($rows as $row) {
            $keysHash = explode(":", $row->key[HabilitationHistoriqueView::KEY_IDDOC]);
            $hab = HabilitationClient::getInstance()->find($row->id);
            $demandeHash = $keysHash[1];
            $demande = $hab->get($demandeHash);

            $historiquePrecedent = $demande->getHistoriquePrecedent($row->key[HabilitationHistoriqueView::KEY_STATUT], $row->key[HabilitationHistoriqueView::KEY_DATE]);

            $historiqueSuivant = $demande->getHistoriqueSuivant($row->key[HabilitationHistoriqueView::KEY_STATUT], $row->key[HabilitationHistoriqueView::KEY_DATE]);

            $declarant = $hab->getDeclarant();
            $adresse = str_replace('"', '', $declarant->adresse);
            $acs = explode('−',$declarant->adresse_complementaire);
            $adresse_complementaire = "";
            $adresse_complementaire_bis = "";
            $adresse_complementaire = str_replace('"', '', $acs[0]);
            if(count($acs) > 1){
                $adresse_complementaire_bis = str_replace('"', '', $acs[1]);
            }

            echo $row->key[HabilitationHistoriqueView::KEY_IDENTIFIANT].";".$declarant->cvi.";".$declarant->siret.";".$declarant->raison_sociale.";".$adresse.";".$adresse_complementaire.";".$adresse_complementaire_bis.";".$declarant->code_postal.";" .$declarant->commune.";".str_replace(";",",",$declarant->email).";".str_replace(";",",",$declarant->telephone_bureau).";".str_replace(";",",",$declarant->telephone_mobile).";".$demande->demande.";".implode(", ", $demande->getActivitesLibelle()).";".$demande->produit_libelle.";".$row->key[HabilitationHistoriqueView::KEY_STATUT].";".$row->key[HabilitationHistoriqueView::KEY_DATE].";".(($historiquePrecedent) ? $historiquePrecedent->statut : null).";".(($historiquePrecedent) ? $historiquePrecedent->date : null).";".(($historiqueSuivant) ? $historiqueSuivant->statut : null).";".(($historiqueSuivant) ? $historiqueSuivant->date : null).";".$row->id.";".$demande->getKey()."\n";
        }
    }


}
