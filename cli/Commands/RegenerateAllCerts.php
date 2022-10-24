<?php

namespace Afeefa\NginxFrontProxy\Commands;

use Afeefa\Component\Cli\Command;
use Afeefa\NginxFrontProxy\Actions\GenerateCert;
use Afeefa\NginxFrontProxy\Actions\ReloadNginx;
use Afeefa\NginxFrontProxy\Helpers;
use Symfony\Component\Filesystem\Path;

class RegenerateAllCerts extends Command
{
    protected function executeCommand()
    {
        $caFilename = Path::join(getenv('NGINX_FRONTPROXY_PATH'), 'servers', 'ca', 'ca.pem');
        if (!file_exists($caFilename)) {
            $this->abortCommand('A certificate authority needs to be created. Please run the setup command.');
        }

        $pathVhosts = Path::join(getenv('NGINX_FRONTPROXY_PATH'), 'servers', 'vhosts');
        if (!file_exists($pathVhosts)) {
            $this->abortCommand('Please run the setup command.');
        }

        $vhosts = Helpers::getAllVhosts($pathVhosts);

        foreach ($vhosts as $vhost) {
            $this->runAction(GenerateCert::class, [
                'serverName' => $vhost['name']
            ]);
        }

        $this->runAction(ReloadNginx::class);
    }
}
