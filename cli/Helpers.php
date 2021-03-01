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
}
