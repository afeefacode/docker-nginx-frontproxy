<?php

namespace Afeefa\NginxFrontProxy\Actions;

use Afeefa\Component\Cli\Action;
use Symfony\Component\Filesystem\Path;

class GenerateCert extends Action
{
    protected function getActionTitle()
    {
        $serverName = $this->getArgument('serverName');
        return 'Generate cert for server ' . $serverName;
    }

    protected function executeAction()
    {
        $serverName = $this->getArgument('serverName');

        $caPath = Path::join(getenv('NGINX_FRONTPROXY_PATH'), 'servers', 'ca');
        $caFilename = Path::join($caPath, 'ca');

        $certPath = Path::join(getenv('NGINX_FRONTPROXY_PATH'), 'servers', 'certs');
        $certFilename = Path::join($certPath, "{$serverName}");

        $extConfTemplate = Path::join(__DIR__, '..', 'templates', 'ext.conf');
        $extConf = Path::join($certPath, "{$serverName}.ext.conf");

        // create a custom ext.conf file
        $this->runProcess("cp {$extConfTemplate} {$extConf}");
        $this->renderFile($extConf, [
            'ALT_NAME' => "DNS:{$serverName},DNS:*.{$serverName}"
        ]);

        $this->runProcesses([
            "openssl genrsa -out {$certFilename}.key 2048",

            "openssl req -subj '/CN={$serverName}' -sha256 -new
                -key {$certFilename}.key -out {$certFilename}.csr",

            "openssl x509 -req -days 900 -sha256
                    -in {$certFilename}.csr
                    -CA {$caFilename}.pem
                    -CAkey {$caFilename}.key
                    -CAcreateserial
                    -CAserial {$certFilename}.seq
                    -out {$certFilename}.crt
                    -extfile {$extConf}",
            "rm {$certFilename}.csr",
            "rm {$extConf}"
        ]);

        $relativePath = Path::makeRelative("{$certFilename}.crt", getenv('NGINX_FRONTPROXY_PATH'));
        $this->printBullet("<info>Finish</info>: created {$relativePath}");
    }
}
