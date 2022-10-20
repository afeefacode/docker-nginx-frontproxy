<?php

namespace Afeefa\NginxFrontProxy\Commands;

use Afeefa\Component\Cli\Command;
use Afeefa\NginxFrontProxy\Helpers;
use Webmozart\PathUtil\Path;

class ListServers extends Command
{
    protected function executeCommand()
    {
        $pathVhosts = Path::join(getenv('NGINX_FRONTPROXY_PATH'), 'servers', 'vhosts');
        if (!file_exists($pathVhosts)) {
            $this->abortCommand('Please run the setup command.');
        }

        $vhosts = Helpers::getAllVhosts($pathVhosts);

        if (count($vhosts)) {
            $vhosts = array_map(fn ($vhost) => "{$vhost['name']} -> {$vhost['container']}", $vhosts);
            sort($vhosts);
            $this->printList($vhosts);
        } else {
            $this->printBullet('No server configured yet.');
        }
    }
}
