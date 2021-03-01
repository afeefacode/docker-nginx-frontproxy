<?php

namespace Afeefa\NginxFrontProxy\Commands;

use Afeefa\Component\Cli\Command;
use Afeefa\NginxFrontProxy\Actions\GenerateCert;
use Afeefa\NginxFrontProxy\Actions\GenerateVhost;
use Afeefa\NginxFrontProxy\Actions\ReloadNginx;
use Webmozart\PathUtil\Path;

class Setup extends Command
{
    protected function executeCommand()
    {
        if (!$this->checkCreateServerDirs()) {
            $this->printActionTitle('Create neccessary directories');
            $this->createServerDirs();
        }

        $caPath = Path::join(getcwd(), 'servers', 'ca');
        $caFilename = Path::join($caPath, 'ca');

        $this->printActionTitle('Generate certificate authority');

        $createCA = true;
        if (file_exists("$caFilename.pem")) {
            $createCA = $this->printConfirm('A certifcate authority file already exists. Overwrite?', false);
        }

        $relativePath = Path::makeRelative("$caFilename.pem", getcwd());

        if ($createCA) {
            $authorityName = $this->printQuestion('What is the name of the authority?', 'afeefa');

            $this->runProcesses([
                "openssl genrsa -out $caFilename.key 2048",
                "openssl req -new -x509 -subj '/CN=$authorityName' -days 900 -key $caFilename.key -sha256 -out $caFilename.pem"
            ]);

            $this->printBullet("<info>Finish</info>: created $relativePath");
        } else {
            $this->printBullet("<info>Finish</info>: $relativePath left unchanged");
        }

        $this->runAction(GenerateCert::class, [
            'serverName' => 'localhost'
        ]);

        $this->runAction(GenerateVhost::class, [
            'serverName' => 'localhost',
            'vhostTemplate' => 'localhost'
        ]);

        $this->runAction(ReloadNginx::class);
    }

    private function checkCreateServerDirs()
    {
        $pathServers = Path::join(getcwd(), 'servers');
        $folders = ['ca', 'certs', 'public', 'vhosts'];

        foreach ($folders as $folderName) {
            $pathFolder = Path::join($pathServers, $folderName);
            if (!file_exists($pathFolder)) {
                return false;
            }
        }

        return true;
    }

    private function createServerDirs()
    {
        $pathServers = Path::join(getcwd(), 'servers');
        $folders = ['ca', 'certs', 'public', 'vhosts'];

        foreach ($folders as $folderName) {
            $pathFolder = Path::join($pathServers, $folderName);
            if (!file_exists($pathFolder)) {
                $this->runProcess("mkdir -p $pathFolder");
                $relativePath = Path::makeRelative("$pathFolder", getcwd());
                $this->printBullet("<info>Finish</info>: created $relativePath");
            }
        }
    }
}
