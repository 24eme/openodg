<?php

class ParcellaireAffectationSendMailAcheteursTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "Document id"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'parcellaire';
        $this->name = 'send-mail-acheteurs';
        $this->briefDescription = "Envoi d'un mail de rappel des pièces non recus";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $routing = clone ProjectConfiguration::getAppRouting();
        $context = sfContext::createInstance($this->configuration);
        $context->set('routing', $routing);

        $parcellaire = ParcellaireAffectationClient::getInstance()->find($arguments['doc_id'], acCouchdbClient::HYDRATE_JSON);

        if(ConfigurationClient::getInstance()->getCampagneManager()->getCurrentNext() != $parcellaire->campagne) {
            return;
        }

        if(!$parcellaire->autorisation_acheteur) {
            return;
        }

        if(isset($parcellaire->papier) && $parcellaire->papier) {
            return;
        }

        if(!$parcellaire->validation || !$parcellaire->validation_odg) {
            return;
        }

        if(!isset($parcellaire->declaration->certification)) {
            return;
        }

        $hasMailToSend = false;
        foreach($parcellaire->acheteurs as $type => $acheteursType) {
            foreach($acheteursType as $acheteur) {
                if($acheteur->cvi == $parcellaire->identifiant) {

                    continue;
                }

                if($acheteur->email_envoye) {
                    continue;
                }

                $hasMailToSend = true;
                break;
            }
        }

        if(!$hasMailToSend) {

            return;
        }

        $parcellaire = ParcellaireAffectationClient::getInstance()->find($arguments['doc_id']);

        foreach($parcellaire->getAcheteursByCVI() as $acheteur) {
            if($acheteur->cvi == $parcellaire->identifiant) {

                continue;
            }

            if($acheteur->email_envoye) {
                continue;
            }

            if(!$acheteur->email) {
                $etablissement = EtablissementClient::getInstance()->find('ETABLISSEMENT-' . $acheteur->cvi, acCouchdbClient::HYDRATE_JSON);
                if($etablissement) {
                    $acheteur->email = $etablissement->email;
                }
            }

            if(!$acheteur->email) {
                echo sprintf("%s ERROR : Email non trouvé %s %s\n", $parcellaire->_id, $acheteur->nom, $acheteur->cvi, $acheteur->email);
            }

            $sended = Email::getInstance()->sendParcellaireAcheteur($parcellaire, $acheteur);
            if($sended) {
                $acheteur->email_envoye = date('Y-m-d');
                $parcellaire->save();
                echo sprintf("%s SUCCESS : Email envoyé à %s %s via %s\n", $parcellaire->_id, $acheteur->nom, $acheteur->cvi, $acheteur->email);
            } else {
                echo sprintf("%s ERROR : Email non envoyé à %s %s via %s\n", $parcellaire->_id, $acheteur->nom, $acheteur->cvi, $acheteur->email);
            }
        }


    }
}