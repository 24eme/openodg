<?php

class GenerationImportParcellaire extends GenerationAbstract
{
    private $oldpath;

    public function generate()
    {
        $this->generation->setStatut(GenerationClient::GENERATION_STATUT_ENCOURS);
        $batch_size = 50;
        $batch_i = 1;

        $oldpath = getcwd();

        // dossier temporaire /tmp/pid-xxxxx/
        $tempdirectory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'gen-'.uniqid();
        mkdir($tempdirectory);
        chdir($tempdirectory);

        // fichier temporaire dans dossier temporaire :
        //      prefix_script_stdin.txt
        //      prefix_script_stderr.txt
        // shell_exec(script) > stdin 2> stderr
        // lien vers les deux fichiers à la fin de la génération

        //$this->generation->setStatut(GenerationClient::GENERATION_STATUT_GENERE);
        //$this->generation->save();

        chdir($oldpath);
        rmdir($tempdirectory);

        $this->generation->setStatut(GenerationClient::GENERATION_STATUT_GENERE);
        $this->generation->save();
    }
}
