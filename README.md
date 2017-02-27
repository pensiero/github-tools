# GitHub Tools module for ZF2

Created by Oscar Fanelli

## Introduction

ZF2 module that provide various tools that connect your application with your github repository via ZF2 console.
What you can do?

- Automatically create a new PR and draft release, based on the diff commits between `develop` and `master` branches
- Mark a repository as deployed

## Installation

GitHub Tools work with composer.
Make sure you have the composer.phar downloaded and you have a composer.json file at the root of your project.
To install it, add the following line into your composer.json file:

```
"require": {
    "pensiero/github-tools": "~1.0"
}
```

## Requirements

* PHP5.6+
* [zend-mvc 2.7](https://github.com/zendframework/zend-mvc)
* [zend-console 2.6](https://github.com/zendframework/zend-console)

## Configuration

Use the [config/github-tools.local.php.dist](../config/github-tools.local.php.dist) as blueprint configuration file:
copy it to the `config/autoload` directory of your ZF2 application and remove the `.dist` extension from its name.

If you are using environment variables to store sensible informations of your projects (like auth keys)
you can use the following ones:
- `GITHUB_ACCESS_TOKEN` will override the `github_access_token` config
- `ENV` will override the `environment` config
- `PROTOCOL` and `HOST` will be combined in order to override the `target_url` config

## Available commands

### Mark a repository as deployed

Will be marked the configurated GitHub repository as deployed

```
php public/index.php github mark-repo-deployed
```


### Create a new release

Will be created a new PR from `github_from_branch` to `github_to_branch` named with the new version name.
Will be created a new draft release for `github_to_branch` named with the new version name.

**Major**

New version name: get the latest release name and increment the *major* part of 1, according to [semver](http://semver.org/).

Example:
- Latest release: `v2.0.3`
- New release: `v3.0.0`

```
php public/index.php github github create-major-release
```

**Minor**

New version name: get the latest release name and increment the *minor* part of 1, according to [semver](http://semver.org/).

Example:
- Latest release: `v2.0.3`
- New release: `v2.1.0`

```
php public/index.php github github create-minor-release
```

**Patch**

New version name: get the latest release name and increment the *patch* part of 1, according to [semver](http://semver.org/).

Example:
- Latest release: `v2.0.3`
- New release: `v2.0.4`

```
php public/index.php github github create-patch-release
```