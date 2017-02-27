<?php
namespace GithubTools\Controller;

use GithubTools\Exception\NotProvidedException;
use GithubTools\Module;
use GithubTools\Util\Version;
use Zend\Mvc\Controller\AbstractActionController;

class ConsoleController extends AbstractActionController
{
    /**
     * @var array
     */
    protected $config;

    private function overrideConfigsWithEnvironmentVariables()
    {
        // github access token
        if (getenv('GITHUB_ACCESS_TOKEN')) {
            $this->config['github_access_token'] = getenv('GITHUB_ACCESS_TOKEN');
        }

        // environment
        if (getenv('ENV')) {
            $this->config['environment'] = getenv('ENV');
        }

        // target url
        if (getenv('PROTOCOL') && getenv('HOST')) {
            $this->config['target_url'] = getenv('PROTOCOL') . '://' . getenv('HOST');
        }
    }

    /**
     * @param array $config
     *
     * @throws NotProvidedException
     */
    public function __construct($config)
    {
        if (!isset($config[Module::CONFIG])) {
            throw new NotProvidedException('No ' . Module::CONFIG . ' configs found, please specify them in your config/autoload folder using the github-tools.local.php.dist blueprint');
        }

        $this->config = $config[Module::CONFIG];

        // override (if not null) configs with environment variables
        $this->overrideConfigsWithEnvironmentVariables();
    }

    /**
     * Mark GitHub repository as deployed
     *
     * @throws NotProvidedException
     */
    public function markRepoDeployedAction()
    {
        if (!$this->config['github_access_token']) {
            throw new NotProvidedException('github_access_token config must be provided in order to mark a GitHub repository as deployed');
        }

        if (!$this->config['github_owner']) {
            throw new NotProvidedException('github_owner config must be provided in order to connect to a repository');
        }

        if (!$this->config['github_repository']) {
            throw new NotProvidedException('github_repository config must be provided in order to connect to a repository');
        }

        if (!$this->config['target_url']) {
            throw new NotProvidedException('target_url config must be provided in order to create a GitHub deploy');
        }

        if (!$this->config['environment']) {
            throw new NotProvidedException('environment config must be provided in order to create a GitHub deploy');
        }

        if (!$this->config['github_to_branch']) {
            throw new NotProvidedException('github_to_branch config must be provided in order to create a GitHub deploy');
        }

        // authenticate to github client
        $client = new \Github\Client();
        $client->authenticate($this->config['github_access_token'], null, \Github\Client::AUTH_HTTP_TOKEN);
        $params = [
            'state'       => 'success',
            'target_url'  => $this->config['target_url'],
            'description' => 'Deployed successful on ' . $this->config['target_url'],
        ];

        // create a deploy
        $deploy = $client
            ->api('deployment')
            ->create(
                $this->config['github_owner'],
                $this->config['github_repository'],
                [
                    'ref'         => $this->config['github_to_branch'],
                    'environment' => $this->config['environment']
                ]
            );

        if (!isset($deploy['id'])) {
            return;
        }

        // mark deploy as success
        $client->api('deployment')->updateStatus($this->config['github_owner'], $this->config['github_repository'], $deploy['id'], $params);
    }

    /**
     * Create a new PR, release and tag
     *
     * @param string $version
     *
     * @throws NotProvidedException
     */
    public function createRelease($version)
    {
        if (!$this->config['github_access_token']) {
            throw new NotProvidedException('github_access_token config must be provided in order to create a GitHub release and PR');
        }

        if (!$this->config['github_owner']) {
            throw new NotProvidedException('github_owner config must be provided in order to connect to a repository');
        }

        if (!$this->config['github_repository']) {
            throw new NotProvidedException('github_repository config must be provided in order to connect to a repository');
        }

        if (!$this->config['github_from_branch']) {
            throw new NotProvidedException('github_from_branch config must be provided in order to create a GitHub release and PR');
        }

        if (!$this->config['github_to_branch']) {
            throw new NotProvidedException('github_to_branch config must be provided in order to create a GitHub release and PR');
        }

        // authenticate to github client
        $client = new \Github\Client();
        $client->authenticate($this->config['github_access_token'], null, \Github\Client::AUTH_HTTP_TOKEN);

        // get the latest release
        $latestRelease = $client->api('repo')->releases()->latest($this->config['github_owner'], $this->config['github_repository']);

        // get the new version (based on latest release name)
        switch ($version) {
            case 'major': {
                $newReleaseVersion = Version::incrementMajor($latestRelease['name']);
            }
            break;
            case 'minor': {
                $newReleaseVersion = Version::incrementMinor($latestRelease['name']);
            }
            break;
            case 'patch': {
                $newReleaseVersion = Version::incrementPatch($latestRelease['name']);
            }
            break;
            default: {
                $newReleaseVersion = Version::incrementMinor($latestRelease['name']);
            }
        }

        $from = $this->config['github_from_branch'] === $this->config['github_to_branch']
            ? $latestRelease['name']
            : $this->config['github_from_branch'];

        // get commits comparing master with develop
        $commits = $client->api('repo')->commits()->compare($this->config['github_owner'], $this->config['github_repository'], $this->config['github_to_branch'], $from);
        $commits = array_map(function($commit) {
            return "- " . $commit['commit']['message'];
        }, $commits['commits']);

        // create a release body with compared commits
        $releaseBody = implode($commits, "\n");
        $releaseBody = preg_replace('/\*.*/', '', $releaseBody);
        $releaseBody = trim(preg_replace('/\n\n/', '', $releaseBody));

        // create a PR
        try {
            if ($this->config['github_from_branch'] === $this->config['github_to_branch']) {
                echo "PR not created because the 'from' and 'to' branches are the same\n";
            }
            else {
                $client
                    ->api('pull_request')
                    ->create($this->config['github_owner'], $this->config['github_repository'], [
                        'base'  => $this->config['github_to_branch'],
                        'head'  => $this->config['github_from_branch'],
                        'title' => $newReleaseVersion,
                        'body'  => '',
                    ]);

                echo sprintf(
                    "Created PR '%s' into %s/%s from %s branch to %s branch\n",
                    $newReleaseVersion,
                    $this->config['github_owner'],
                    $this->config['github_repository'],
                    $this->config['github_from_branch'],
                    $this->config['github_to_branch']
                );
            }
        }
        catch (\Exception $e) {
            echo "Error while creating a new GitHub PR\n";
        }

        // create a release
        try {
            $client
                ->api('repo')
                ->releases()
                ->create($this->config['github_owner'], $this->config['github_repository'], [
                    'tag_name'         => $newReleaseVersion,
                    'name'             => $newReleaseVersion,
                    'target_commitish' => $this->config['github_to_branch'],
                    'draft'            => true,
                    'body'             => $releaseBody,
                ]);

            echo sprintf(
                "Created draft release '%s' into %s/%s for %s branch\n",
                $newReleaseVersion,
                $this->config['github_owner'],
                $this->config['github_repository'],
                $this->config['github_to_branch']
            );
        }
        catch (\Exception $e) {
            echo "Error while creating a new GitHub release\n";
        }
    }

    /**
     * Create a GitHub major release
     */
    public function createMajorReleaseAction()
    {
        return $this->createRelease('major');
    }

    /**
     * Create a GitHub minor release
     */
    public function createMinorReleaseAction()
    {
        return $this->createRelease('minor');
    }

    /**
     * Create a GitHub patch release
     */
    public function createPatchReleaseAction()
    {
        return $this->createRelease('patch');
    }

}
