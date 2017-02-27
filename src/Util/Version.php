<?php
namespace GithubTools\Util;

class Version
{
    /**
     * Increment major version of a semver release
     *
     * @param $version
     *
     * @return string
     */
    public static function incrementMajor($version)
    {
        $version = str_replace('v', '', $version);

        list($major, $minor, $patch) = explode('.', $version);

        return 'v' . implode('.', [((int) $major) + 1, 0, 0]);
    }

    /**
     * Increment minor version of a semver release
     *
     * @param $version
     *
     * @return string
     */
    public static function incrementMinor($version)
    {
        $version = str_replace('v', '', $version);

        list($major, $minor, $patch) = explode('.', $version);

        return 'v' . implode('.', [(int) $major, ((int) $minor) + 1, 0]);
    }

    /**
     * Increment patch version of a semver release
     *
     * @param $version
     *
     * @return string
     */
    public static function incrementPatch($version)
    {
        $version = str_replace('v', '', $version);

        list($major, $minor, $patch) = explode('.', $version);

        return 'v' . implode('.', [(int) $major, (int) $minor, ((int) $patch) + 1]);
    }
}