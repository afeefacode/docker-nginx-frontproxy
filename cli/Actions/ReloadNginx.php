<?php

namespace Afeefa\NginxFrontProxy\Actions;

use Afeefa\Component\Cli\Action;
use Afeefa\NginxFrontProxy\Helpers;

class ReloadNginx extends Action
{
    protected function getActionTitle()
    {
        $nginxIsRunning = Helpers::containerIsRunning('nginx-frontproxy');
        if ($nginxIsRunning) {
            return 'Reload Nginx';
        }
        return '';
    }

    protected function executeAction()
    {
        $nginxIsRunning = Helpers::containerIsRunning('nginx-frontproxy');
        if ($nginxIsRunning) {
            $this->runProcess('docker exec nginx-frontproxy service nginx reload');
        }
    }
}
