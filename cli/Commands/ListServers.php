<?php

namespace Afeefa\NginxFrontProxy\Commands;

use Afeefa\Component\Cli\Command;
use Webmozart\PathUtil\Path;

class ListServers extends Command
{
    protected function executeCommand()
    {
        $pathVhosts = Path::join(getcwd(), 'servers', 'vhosts');
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

        if (count($vhosts)) {
            $this->printList($vhosts);
        } else {
            $this->printBullet('No server configured yet.');
        }
    }
}
