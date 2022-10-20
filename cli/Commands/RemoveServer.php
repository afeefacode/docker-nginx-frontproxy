<?php

namespace Afeefa\NginxFrontProxy\Commands;

use Afeefa\Component\Cli\Command;
use Afeefa\NginxFrontProxy\Actions\ReloadNginx;
use Symfony\Component\Console\Input\InputArgument;
use Webmozart\PathUtil\Path;

class RemoveServer extends Command
{
    protected function setArguments()
    {
        $this
            ->addArgument(
                'serverName',
                InputArgument::OPTIONAL,
                'The domain name'
            );
    }

    protected function executeCommand()
    {
        $pathVhosts = Path::join(getenv('NGINX_FRONTPROXY_PATH'), 'servers', 'vhosts');
        if (!file_exists($pathVhosts)) {
            $this->abortCommand('Please run the setup command.');
        }

        $vhosts = [];

        foreach (new \DirectoryIterator($pathVhosts) as $vhostFileInfo) {
            if (!$vhostFileInfo->isDot() && $vhostFileInfo->isFile() && preg_match('/\.conf/', $vhostFileInfo)) {
                $serverName = preg_replace('/.conf$/', '', $vhostFileInfo);
                if ($serverName !== 'localhost') {
                    $vhosts[] = $serverName;
                }
            }
        }

        if (!count($vhosts)) {
            $this->abortCommand('There is no server to remove');
        }

        $serverName = $this->getArgument('serverName');
        if (!$serverName || !in_array($serverName, $vhosts)) {
            $serverName = $this->printChoice('Please select a vhost to remove', $vhosts);
        }

        $pathServers = Path::join(getenv('NGINX_FRONTPROXY_PATH'), 'servers');

        $this->runProcesses([
            "rm -f {$pathServers}/certs/{$serverName}.crt",
            "rm -f {$pathServers}/certs/{$serverName}.key",
            "rm -rf {$pathServers}/public/{$serverName}",
            "rm -rf {$pathServers}/vhosts/{$serverName}.conf"
        ]);

        $this->runAction(ReloadNginx::class);
    }
}
