<?php

namespace Afeefa\NginxFrontProxy;

use Symfony\Component\Process\Process;

class Helpers
{
    public static function containerIsRunning(string $containerName): bool
    {
        return self::containerExists($containerName, true);
    }

    public static function containerExists(string $containerName, bool $isRunning = false): bool
    {
        $command = 'docker ps ' . ($isRunning ? '' : '-a') . ' -q -f name=' . $containerName . '$';
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(3600);
        $process->run();
        return $process->getOutput() ? true : false;
    }

    public static function getAllVhosts(string $pathVhosts): array
    {
        $vhosts = [];

        foreach (new \DirectoryIterator($pathVhosts) as $vhostFileInfo) {
            if (!$vhostFileInfo->isDot() && $vhostFileInfo->isFile() && preg_match('/\.conf/', $vhostFileInfo)) {
                $serverName = preg_replace('/.conf$/', '', $vhostFileInfo);
                if ($serverName !== 'localhost') {
                    $content = file_get_contents($vhostFileInfo->getRealPath());
                    $container = preg_replace('/.+set \$example "(.+?)".+/s', '$1', $content);
                    $container = preg_replace('/172.17.0.1/', 'localhost', $container);

                    $vhosts[] = [
                        'name' => $serverName,
                        'container' => $container
                    ];
                }
            }
        }

        return $vhosts;
    }
}
