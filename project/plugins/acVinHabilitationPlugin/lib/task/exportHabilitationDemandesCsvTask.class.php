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
        $this->name = 'habilitation-demandes';
        $this->briefDescription = 'Export des demandes habilitations';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        echo "Date;Statut;Identifiant;Description;Commentaire;Auteur;Id Demande;Type;Date habilitation;Hash Produit;Libelle Produit;Activite\n";
        $rows = HabilitationHistoriqueView::getInstance()->getAll();
        foreach($rows as $row) {
            if (preg_match('/demandes/', $row->key[HabilitationHistoriqueView::KEY_IDDOC])) {
                $nb = count($row->key);
                $i = 0;
                foreach ($row->key as $key) {
                    if (is_array($key)) {
                        $key = implode(',', str_replace(',', '-', $key));
                    }
                    $key = preg_replace('/"/', '\"', $key);
                    echo str_replace(';', '-', $key);
                    $i++;
                    if ($i < $nb) {
                        echo ";";
                    }
                }
                echo "\n";
            }
        }
    }
    
    
}
