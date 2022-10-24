<?php

namespace Afeefa\NginxFrontProxy\Commands;

use Afeefa\Component\Cli\Command;
use Afeefa\NginxFrontProxy\Actions\GenerateCert;
use Afeefa\NginxFrontProxy\Actions\GenerateVhost;
use Afeefa\NginxFrontProxy\Actions\ReloadNginx;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Filesystem\Path;

class AddServer extends Command
{
    protected function setArguments()
    {
        $this
            ->addArgument(
                'serverName',
                InputArgument::OPTIONAL,
                'The domain name'
            )
            ->addArgument(
                'containerName',
                InputArgument::OPTIONAL,
                'The container name'
            );
    }

    protected function executeCommand()
    {
        $caFilename = Path::join(getenv('NGINX_FRONTPROXY_PATH'), 'servers', 'ca', 'ca.pem');
        if (!file_exists($caFilename)) {
            $this->abortCommand('A certificate authority needs to be created. Please run the setup command.');
        }

        $serverName = $this->getArgument('serverName');
        if (!$serverName) {
            $serverName = $this->printQuestion('What is the domain name of the server?');
        }

        if ($serverName === 'localhost') {
            $this->abortCommand('localhost cannot be added.');
        }

        $containerName = $this->getArgument('containerName');
        if (!$containerName) {
            $containerName = $this->printQuestion('What is the container name of the server?');
        }

        // rewrite localhost to host ip
        $containerName = preg_replace('/localhost/', '172.17.0.1', $containerName);
        $containerName = preg_replace('/127.0.0.1/', '172.17.0.1', $containerName);

        $this->runAction(GenerateCert::class, [
            'serverName' => $serverName
        ]);

        $this->runAction(GenerateVhost::class, [
            'serverName' => $serverName,
            'containerName' => $containerName,
            'vhostTemplate' => 'proxy'
        ]);

        $this->runAction(ReloadNginx::class);
    }
}
