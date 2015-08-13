<?php

class FixDegustateurProduitsTask extends sfBaseTask
{

    const CSV_EXPERT_ALSACE         = 0;
    const CSV_EXPERT_CREMANT        = 1;
    const CSV_EXPERT_VT_SGN         = 2;
    const CSV_EXPERT_MAGW           = 3;
    const CSV_EXPERT_GC             = 4;
    const CSV_IDENTIFIANT           = 5;

    protected function configure()
    {
        $this->addArguments(array(
           new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "Donnees au format CSV")
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'fix';
        $this->name = 'degustateur-produits';
        $this->briefDescription = "Corrige le parcellaire passé en parametre";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        foreach(file($arguments['csv']) as $line) {
            $data = str_getcsv($line, ';');

            $compte = CompteClient::getInstance()->find('COMPTE-D'.sprintf("%06d", $data[self::CSV_IDENTIFIANT]));

            if(!$compte) {
                echo sprintf("ERROR;Compte non trouvé %s;%s", $data[self::CSV_IDENTIFIANT], $line);
                continue;
            }

            if(trim($data[self::CSV_EXPERT_ALSACE]) == "x") {
                //$compte->infos->produits->add("-declaration-certification-genre-appellation_ALSACE", "AOC Alsace");
                //echo sprintf("INFO;Expert alsace;%s\n", $line);
            }

            if(trim($data[self::CSV_EXPERT_CREMANT])) {
                $compte->infos->produits->add("-declaration-certification-genre-appellation_CREMANT", "AOC Crémant d'Alsace");
                echo sprintf("INFO;Expert crémant;%s", $line);
            }

            if(trim($data[self::CSV_EXPERT_VT_SGN])) {
                //$compte->infos->produits->add("-declaration-certification-genre-appellation_VTSGN", "VT / SGN");
                //echo sprintf("INFO;Expert vt sgn;%s\n", $line);
            }

            if(trim($data[self::CSV_EXPERT_MAGW])) {
                $compte->infos->produits->add("-declaration-certification-genre-appellation_MARC", "Marc de Gewurztraminer");
                echo sprintf("INFO;Expert marc de gewurtz;%s", $line);
            }

            if(trim($data[self::CSV_EXPERT_GC])) {
                //echo sprintf("%s\n", $data[self::CSV_EXPERT_GC]);
                $groupes = explode("-", $data[self::CSV_EXPERT_GC]);

                if(count($groupes) > 0) {
                    $compte->infos->produits->add("-declaration-certification-genre-appellation_GRDCRU", "AOC Alsace Grands Crus");
                }

                foreach($groupes as $groupe) {
                    $grdcrus = $this->getGrdCrusByGroupe(trim($groupe));
                    foreach($grdcrus as $key => $libelle) {
                        $compte->infos->produits->add($key, $libelle);
                        echo sprintf("INFO;Expert grand gru;%s;%s;%s", $key, $libelle ,$line);
                    }
                }
            }

            $compte->save();
        }
    }

    public function getGrdCrusByGroupe($groupe) {
        if($groupe === "1") {

            return array(
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu01" => "AOC Alsace Grands Crus Altenberg de Bergbieten",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu26" => "AOC Alsace Grands Crus Altenberg de Wolxheim",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu11" => "AOC Alsace Grands Crus Kastelberg",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu16" => "AOC Alsace Grands Crus Moenchberg",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu42" => "AOC Alsace Grands Crus Steinklotz",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu25" => "AOC Alsace Grands Crus Wiebelsberg",

            );
        }

        if($groupe === "2") {

            return array(
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu02" => "AOC Alsace Grands Crus Altenberg de Bergheim",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu51" => "AOC Alsace Grands Crus Kaefferkopf",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu10" => "AOC Alsace Grands Crus Kanzlerberg",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu37" => "AOC Alsace Grands Crus Praelatenberg",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu45" => "AOC Alsace Grands Crus Winzenberg",
            );
        }

        if($groupe === "3") {

            return array(
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu03" => "AOC Alsace Grands Crus Brand",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu04" => "AOC Alsace Grands Crus Eichberg",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu08" => "AOC Alsace Grands Crus Hatschbourg",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu22" => "AOC Alsace Grands Crus Sommerberg",
            );
        }

        if($groupe === "4") {

            return array(
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu49" => "AOC Alsace Grands Crus Bruderthal",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu27" => "AOC Alsace Grands Crus Engelberg",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu47" => "AOC Alsace Grands Crus Zotzenberg",
            );
        }

        if($groupe === "5") {

            return array(
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu50" => "AOC Alsace Grands Crus Florimont",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu30" => "AOC Alsace Grands Crus Mambourg",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu21" => "AOC Alsace Grands Crus Schlossberg",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu41" => "AOC Alsace Grands Crus Steingrubler",
            );
        }

        if($groupe === "6") {

            return array(
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu05" => "AOC Alsace Grands Crus Geisberg",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu28" => "AOC Alsace Grands Crus Frankstein",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu14" => "AOC Alsace Grands Crus Kirchberg de Ribeauvillé",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu32" => "AOC Alsace Grands Crus Marckrain",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu34" => "AOC Alsace Grands Crus Osterberg",
            );
        }

        if($groupe === "FRA") {

            return array(
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu28" => "AOC Alsace Grands Crus Frankstein",
            );
        }

        if($groupe === "7") {

            return array(
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu29" => "AOC Alsace Grands Crus Froehn",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu13" => "AOC Alsace Grands Crus Kirchberg de Barr",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu33" => "AOC Alsace Grands Crus Muenchberg",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu39" => "AOC Alsace Grands Crus Sporen",
            );
        }

        if($groupe === "8") {

            return array(
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu48" => "AOC Alsace Grands Crus Furstentum",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu09" => "AOC Alsace Grands Crus Hengst",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu23" => "AOC Alsace Grands Crus Sonnenglanz",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu44" => "AOC Alsace Grands Crus Wineck-Schlossberg",
            );
        }

        if($groupe === "9") {

            return array(
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu07" => "AOC Alsace Grands Crus Goldert",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu36" => "AOC Alsace Grands Crus Pfingstberg",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu40" => "AOC Alsace Grands Crus Steinert",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu43" => "AOC Alsace Grands Crus Vorbourg",
            );
        }

        if($groupe === "10") {

            return array(
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu12" => "AOC Alsace Grands Crus Kessler",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu15" => "AOC Alsace Grands Crus Kitterlé",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu20" => "AOC Alsace Grands Crus Saering",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu24" => "AOC Alsace Grands Crus Spiegel",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu26" => "AOC Alsace Grands Crus Zinnkoepfle",
            );
        }

        if($groupe === "11") {

            return array(
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu06" => "AOC Alsace Grands Crus Gloeckelberg",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu31" => "AOC Alsace Grands Crus Mandelberg",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu19" => "AOC Alsace Grands Crus Rosacker",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu38" => "AOC Alsace Grands Crus Schoenenbourg",
            );
        }

        if($groupe === "12") {

            return array(
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu17" => "AOC Alsace Grands Crus Ollwiller",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu35" => "AOC Alsace Grands Crus Pfersigberg",
                "-declaration-certification-genre-appellation_GRDCRU-mention-lieu18" => "AOC Alsace Grands Crus Rangen",
            );
        }

        throw new sfException(sprintf("Groupe %s introuvable", $groupe));
    }
}