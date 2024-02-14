<?php

class GenerationShell extends GenerationAbstract
{
    const DESC_STDIN = 0;
    const DESC_STDOUT = 1;
    const DESC_STDERR = 2;

    private $webdir = '/generation/';
    private $oldpath;

    public function generate()
    {
        $this->generation->setStatut(GenerationClient::GENERATION_STATUT_ENCOURS);
        $this->generation->save();

        $batch_size = 50;
        $batch_i = 1;

        $oldpath = getcwd();

        // dossier temporaire /tmp/pid-xxxxx/
        $tempdirectory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'gen-'.uniqid();
        mkdir($tempdirectory);
        chdir($tempdirectory);

        // fichier temporaire dans dossier temporaire :
        //      prefix_script_stdout.txt
        //      prefix_script_stderr.txt
        $stdout = new SplFileObject('stdout.txt', 'w');
        $stderr = new SplFileObject('stderr.txt', 'w');

        // shell_exec(script) > stdin 2> stderr
        $process = proc_open(
            $this->buildCommandLine(),
            $this->buildDescriptors(null, $stdout, $stderr),
            $pipes
        );

        if (is_resource($process) === false) {
            $this->generation->setStatut(GenerationClient::GENERATION_STATUT_ENERREUR);
            $this->generation->setMessage("Impossible de lancer le processus");
            $this->generation->save();
            exit;
        }

        $returnvalue = proc_close($process);

        // lien vers les fichiers à la fin de la génération
        $prefix = $this->webdir.$this->generation->date_emission;
        foreach (glob('*') as $generatedFile) {
            rename($generatedFile, sfConfig::get('sf_web_dir').$prefix.'_'.$generatedFile);
            $this->generation->add('fichiers')->add(urlencode($prefix.'_'.$generatedFile), $this->formatName($generatedFile));
        }

        // clean
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

    private function buildCommandLine()
    {
        if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
            return $this->generation->arguments->toArray();
        } else {
            $cmde = [];
            foreach ($this->generation->arguments as $arg) {
                $cmde[] = escapeshellarg($arg);
            }
            return implode(' ', $cmde);
        }
    }

    private function formatName($file)
    {
        if (strpos($file, 'stderr') !== false) {
            return "Log d'erreur de la génération";
        }

        if (strpos($file, 'stdout') !== false) {
            return "Log de sortie de la génération";
        }

        return $file;
    }
}
