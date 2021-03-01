<?php

namespace Afeefa\NginxFrontProxy\Actions;

use Afeefa\Component\Cli\Action;
use Webmozart\PathUtil\Path;

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

        $caPath = Path::join(getcwd(), 'servers', 'ca');
        $caFilename = Path::join($caPath, 'ca');

        $certPath = Path::join(getcwd(), 'servers', 'certs');
        $certFilename = Path::join($certPath, "$serverName");

        $extConf = Path::join(__DIR__, '..', 'templates', 'ext.conf');
        $altName = "DNS:$serverName,DNS:*.$serverName";

        $this->runProcesses([
            "openssl genrsa -out $certFilename.key 2048",

            "openssl req -subj '/CN=$serverName' -extensions v3_req -sha256 -new
                -key $certFilename.key -out $certFilename.csr",

            "export ALTNAME=$altName;
                openssl x509 -req -days 900 -sha256
                    -in $certFilename.csr
                    -CA $caFilename.pem
                    -CAkey $caFilename.key
                    -CAcreateserial
                    -CAserial $certFilename.seq
                    -out $certFilename.crt
                    -extfile $extConf",
            "rm $certFilename.csr",
            "rm $certFilename.seq"
        ]);

        $relativePath = Path::makeRelative("$certFilename.crt", getcwd());
        $this->printBullet("<info>Finish</info>: created $relativePath");
    }
}
