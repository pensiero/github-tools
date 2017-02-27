<?php
namespace GithubTools\Util;

class Version
{
    /**
     * Get all the version parts (create them if missing)
     *
     * @param $version
     *
     * @return array
     */
    public static function getVersionParts($version)
    {
        $version = str_replace('v', '', $version);

        $versionParts = explode('.', $version);

        $major = isset($versionParts[0]) ? (int) $versionParts[0] : 0;
        $minor = isset($versionParts[1]) ? (int) $versionParts[1] : 0;
        $patch = isset($versionParts[2]) ? (int) $versionParts[2] : 0;

        return [$major, $minor, $patch];
    }

    /**
     * Increment major version of a semver release
     *
     * @param $version
     *
     * @return string
     */
    public static function incrementMajor($version)
    {
        list($major, $minor, $patch) = self::getVersionParts($version);

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
        list($major, $minor, $patch) = self::getVersionParts($version);

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
        list($major, $minor, $patch) = self::getVersionParts($version);

        return 'v' . implode('.', [(int) $major, (int) $minor, ((int) $patch) + 1]);
    }
}