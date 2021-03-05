<?php

use Afeefa\Component\Cli\Cli;
use Afeefa\NginxFrontProxy\Commands\AddServer;
use Afeefa\NginxFrontProxy\Commands\ListServers;
use Afeefa\NginxFrontProxy\Commands\RemoveServer;
use Afeefa\NginxFrontProxy\Commands\Setup;

require_once __DIR__ . '/../vendor/autoload.php';

(new Cli('Nginx Frontproxy'))
    ->command('setup', Setup::class, 'Run initial setup')
    ->command('list-servers', ListServers::class, 'List all servers')
    ->command('add-server', AddServer::class, 'Add a server')
    ->command('remove-server', RemoveServer::class, 'Remove a server')
    ->run();
