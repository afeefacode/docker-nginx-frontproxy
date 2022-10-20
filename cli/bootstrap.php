<?php

use Afeefa\Component\Cli\Cli;
use Afeefa\NginxFrontProxy\Commands\AddServer;
use Afeefa\NginxFrontProxy\Commands\ListServers;
use Afeefa\NginxFrontProxy\Commands\RegenerateAllCerts;
use Afeefa\NginxFrontProxy\Commands\RemoveServer;
use Afeefa\NginxFrontProxy\Commands\Setup;
use Webmozart\PathUtil\Path;

require_once __DIR__ . '/../vendor/autoload.php';

putenv('NGINX_FRONTPROXY_PATH=' . Path::join(__DIR__, '..'));

(new Cli('Nginx Frontproxy'))
    ->command('setup', Setup::class, 'Run initial setup')
    ->command('list-servers', ListServers::class, 'List all servers')
    ->command('add-server', AddServer::class, 'Add a server')
    ->command('remove-server', RemoveServer::class, 'Remove a server')
    ->command('renew-certs', RegenerateAllCerts::class, 'Regenerate all certs')
    ->run();
