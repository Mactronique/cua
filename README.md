# cua
Composer Update Analyzer

[![Build Status](https://travis-ci.org/Mactronique/cua.svg?branch=master)](https://travis-ci.org/Mactronique/cua) [![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

This utility can 
* Read the `composer.lock` to get all library used by your project and run `composer update --dry-run` to get all necessary install, update or remove actions.
* Run security-checker to get all library actually installed with know security issues. __This feature send the `composer.lock` file at the remote service__.

The output can be a `yaml` file or DBAL table.

## Requirements

* [composer](https://getcomposer.org/)
* [PHP 5.6+](http://php.net)
* [security-checker](https://security.symfony.com/)

## Recommended software

> For Linux only.

* [Docker](https://docs.docker.com/install/)
* [Docker compose](https://docs.docker.com/compose/install/)
* [Make](https://en.wikipedia.org/wiki/Makefile)

## Install

* Clone git repository or download tarball.
* install dependency by `composer install -o`

## Configure

Add a file `cua.yml` (or copy the `cual.yml.dist` file) at the root cua folder.

Put content:

```
composer_path: /usr/local/bin/composer # the path to the composer executable
security_checker_path: /usr/bin/security-checker # The path to security-checker executable
                                                 # (set 'internal' if you want use the Internal Security Checker)
persistance:
    format: DbalPersistance # Persistence
    parameters:
        dbname: 'deps'                      # DBAL Database name
        user: 'root'                        # DBAL database username
        password: 'root'                    # DBALbatabase user password
        host: 'localhost'                   # DBAL server name or IP
        driver: 'pdo_mysql'                 # DBAL Driver name
        table_name: 'dependencies'          # the table name for DBAL persistence
        table_name_security: 'security'     # the table name for DBAL persistence

        path: ./all.yml                     # for YamlFile only
        path_security: ./all_security.yml   # for YamlFile only

project_provider:
    type: file
    parameters:
        path: projects.yml # Location of the file project list of root cua install
```

Add a file `project.yml` (or copy the `project.yml.dist` file) at the root cua folder.

Put content:

```
projects:
    'project_name':
        path: /path/to/project
        check_dependencies: true   # Optional, by default : true
        lock_path: ./composer.lock # Optional, by default : ./composer.lock. Set the location of composer.lock file from the project path.
        check_security: false      # Optional, by default : false. Enable this project for command security
        php_path: php7.2           # Optional, by default : php. The php executable name (or path) for run security-checker and composer
```

### Store in file

In the `cua.yml` file, set the property `persistance.format` to the `YamlFile`.

Define two properties `path` and `path_security` with the path when you want store the result.

> Note : The content is overwritten for each execution.

Configuration example:

```
[...]
persistance:
    format: YamlFile # Persistence
    parameters:
        path: ./all.yml                     # The content of library update
        path_security: ./all_security.yml   # The content of security issues
[...]
```

### Store in Relational Databases (with DBAL)

In the `cua.yml` file, set the property `persistance.format` to the `DbalPersistance`.

All script for creating tables in your database is in this folder `src/Sql/`. If your RDBMS is not present, you cal use on script and update it for your usage.

The parameters are the same for [Doctrine DBAL](https://www.doctrine-project.org/projects/doctrine-dbal/en/2.9/reference/configuration.html) with two additional fields `table_name` and `table_name_security`.

> Note : The PHP used for running the cua application do need the driver for the database.

Configuration example:

```
[...]
persistance:
    format: DbalPersistance # Persistence
    parameters:
        dbname: 'deps'                      # DBAL Database name
        user: 'root'                        # DBAL database username
        password: 'root'                    # DBALbatabase user password
        host: 'localhost'                   # DBAL server name or IP
        driver: 'pdo_mysql'                 # DBAL Driver name
        table_name: 'dependencies'          # the table name for the dependencies DBAL persistence
        table_name_security: 'security'     # the table name for the security DBAL persistence
[...]
```


### Use with Redmine Cua Plugin (deprecated)

Set the format to `RedmineCuaPersistance` and `table_name` to `cua_dependencies`

In this case, the `project_name` set into the config file do the same project identifier from redmine.


### File Project Provider

This file contains all configuration for each project.

Set the property `project_provider.type` to `file`.

Set the property `project_provider.parameters.path` to the name of file contains all projects configuration.

> Note : The file path is relative to the cua installation folder.

Exemple of configuration:

```
[...]
project_provider:
    type: file
    parameters:
        path: projects.yml # Location of the file project list of root cua install
```


### Dbal Project Provider

You can store the configuration into a database. The SQL script present into the folder `src/Sql` contains the table `projects` description.

The table fields descriptions:
* The field `code` of type string and 10 characters maximum of length is needed and contain a unique identity for the project
* The field `name` of type string and 100 characters maximum of length is needed and contain user-friendly project name
* The field `path` of type string and 255 characters maximum of length is needed and contain the path to the root project folder
* The field `lock_path` of type string and 255 characters maximum of length is needed and contain the relative path (from the root project path) to the `composer.lock` file.
* The field `php_path` of type string and 255 characters maximum of length is needed and contain the PHP executable name or full path.
* The field `private_dependencies` of type text is optional. If defined this field do contain a JSON representation of the array. This array contains all private dependency name. Used only for the security check.
* The field `private_dependencies_strategy` of type string and 10 characters maximum of length is optional, the default value is `remove`. The value can be `remove` or `hash`. This defines the behavior for the private dependency before send the `composer.lock` file for security checks.
* The field `check_dependencies` of type tinyint(1) is optional, if defined to 1, enable the dependency check for this project.
* The field `check_security` of type tinyint(1) is optional, if defined to 1, enable the security check for this project.
* The field `updated_at` of type datetime is needed and contains the last update date and time.

Example of configuration:

```
[...]
project_provider:
    type: file
    parameters:
        working_dir: /sources                   # The root folder used in prefix for project path
        db:
            dbname: 'deps'                      # DBAL Database name
            user: 'root'                        # DBAL database username
            password: 'root'                    # DBALbatabase user password
            host: 'localhost'                   # DBAL server name or IP
            driver: 'pdo_mysql'                 # DBAL Driver name
        table_name: projects                    # The table name for read the project configuration
```


## Check project configuration

Open console, go to the cua root folder and type :

```
php ./cua project:list
```

This command print a table with all project configured and details.

## Usage for check dependencies

Open console, go to the cua root folder and type :

```
php ./cua check
```

This command launch the process for all setting project and store in persistence.

## Usage for check security

Open console, go to the cua root folder and type :

```
php ./cua security
```

This command launch the process for all setting project and store in persistence.



# Contribute

If you want to contribute, please fork my repo, add features or fix bugs and create a new pull request.


# Todo

[ ] Add script to creating tables on other Database (postgre, mssql, etc.).

[ ] Get PHP and extentions requirements by the project.

