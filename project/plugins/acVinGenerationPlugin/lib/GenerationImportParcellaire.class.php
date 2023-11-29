<?php

class GenerationImportParcellaire extends GenerationAbstract
{
    const script = '/path/to/script/xxxxxxxxxxxxxxx/bin/script.sh';
    const DESC_STDIN = 0;
    const DESC_STDOUT = 1;
    const DESC_STDERR = 2;
    private $oldpath;

    public function generate()
    {
        /* $this->generation->setStatut(GenerationClient::GENERATION_STATUT_ENCOURS); */
        $batch_size = 50;
        $batch_i = 1;

        $oldpath = getcwd();
        $script = new SplFileObject(self::script);

        // dossier temporaire /tmp/pid-xxxxx/
        $tempdirectory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'gen-'.uniqid();
        mkdir($tempdirectory);
        chdir($tempdirectory);

        // fichier temporaire dans dossier temporaire :
        //      prefix_script_stdout.txt
        //      prefix_script_stderr.txt
        $stdout = new SplFileObject(KeyInflector::slugify($script->getFilename()).'_stdout.txt', 'w');
        $stderr = new SplFileObject(KeyInflector::slugify($script->getFilename()).'_stderr.txt', 'w');

        // shell_exec(script) > stdin 2> stderr
        $process = proc_open(
            $this->buildCommandLine(['bash', $script->getRealPath()], ['9907902400']),
            $this->buildDescriptors(null, $stdout, $stderr),
            $pipes
        );

        if (is_resource($process)) {
            $returnvalue = proc_close($process);
        } else {
            $this->generation->setStatut(GenerationClient::GENERATION_STATUT_ENERREUR);
            $this->generation->setMessage("Impossible de lancer le processus");
            $this->generation->save();
            exit;
        }

        // lien vers les deux fichiers à la fin de la génération

        //$this->generation->setStatut(GenerationClient::GENERATION_STATUT_GENERE);
        //$this->generation->save();

        chdir($oldpath);
        rmdir($tempdirectory);

        $this->generation->setStatut(GenerationClient::GENERATION_STATUT_GENERE);
        $this->generation->save();
    }

    private function buildDescriptors($stdin = null, $stdout = null, $stderr = null)
    {
        $in  = ($stdin)  ? ['file', $stdin->getRealPath()]  : ['pipe'];
        $out = ($stdout) ? ['file', $stdout->getRealPath()] : ['pipe'];
        $err = ($stderr) ? ['file', $stderr->getRealPath()] : ['pipe'];

        return [
            self::DESC_STDIN  => array_merge($in,  ['r']),
            self::DESC_STDOUT => array_merge($out, ['w']),
            self::DESC_STDERR => array_merge($err, ['a']),
        ];
    }

    private function buildCommandLine($cmd, $args = [])
    {
        if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
            return array_merge($cmd, $args);
        } else {
            $cmde = null;
            foreach (array_merge($cmd, $args) as $arg) {
                $cmde[] = escapeshellarg($arg);
            }
            return implode(' ', $cmde);
        }
    }
}
