<?php

namespace Afeefa\NginxFrontProxy\Actions;

use Afeefa\Component\Cli\Action;
use Webmozart\PathUtil\Path;

class GenerateVhost extends Action
{
    protected function getActionTitle()
    {
        $serverName = $this->getArgument('serverName');
        return 'Generate vhost for server ' . $serverName;
    }

    protected function executeAction()
    {
        $serverName = $this->getArgument('serverName');
        $containerName = $this->getArgument('containerName');
        $template = $this->getArgument('vhostTemplate');

        $templateVhost = Path::join(getenv('NGINX_FRONTPROXY_PATH'), 'vhost-templates', $template, 'vhost.conf');
        $vhostConf = Path::join(getenv('NGINX_FRONTPROXY_PATH'), 'servers', 'vhosts', "{$serverName}.conf");

        $this->runProcess("cp {$templateVhost} {$vhostConf}");

        $this->renderFile($vhostConf, [
            'SERVERNAME' => $serverName,
            'CONTAINERNAME' => $containerName
        ]);

        $relativePath = Path::makeRelative($vhostConf, getenv('NGINX_FRONTPROXY_PATH'));
        $this->printBullet("<info>Finish</info>: created {$relativePath}");

        $templateHtml = Path::join(getenv('NGINX_FRONTPROXY_PATH'), 'vhost-templates', $template, 'vhost.html');

        if (file_exists($templateHtml)) {
            $indexPath = Path::join(getenv('NGINX_FRONTPROXY_PATH'), 'servers', 'public', $serverName);
            $indexFile = Path::join($indexPath, 'index.html');

            $this->printActionTitle('Generate index.html for server ' . $serverName);

            $this->runProcesses([
                "mkdir -p {$indexPath}",
                "cp {$templateHtml} {$indexFile}"
            ]);

            $this->renderFile($indexFile, [
                'SERVERNAME' => $serverName,
                'CONTAINERNAME' => $containerName
            ]);

            $relativePath = Path::makeRelative($indexFile, getenv('NGINX_FRONTPROXY_PATH'));
            $this->printBullet("<info>Finish</info>: created {$relativePath}");
        }
    }
}
