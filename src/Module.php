<?php
namespace GithubTools;

use Zend\Console\Adapter\AdapterInterface as Console;

class Module
{
    const CONFIG = 'github_tools';

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * All methods available in the console
     *
     * @param Console $console
     *
     * @return array
     */
    public function getConsoleUsage(Console $console)
    {
        return [
            'github mark-repo-deployed' => 'Mark a repository as deployed',
            'github create-major-release' => 'Create PR and major release with the diff commits between "develop" and "master" branch',
            'github create-minor-release' => 'Create PR and minor release with the diff commits between "develop" and "master" branch',
            'github create-patch-release' => 'Create PR and patch release with the diff commits between "develop" and "master" branch',
        ];
    }
}