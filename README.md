# cua
Composer Update Analyser

[![Build Status](https://travis-ci.org/Mactronique/cua.svg?branch=master)](https://travis-ci.org/Mactronique/cua) [![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

This utility can 
* read the `composer.lock` for get all library used by your project and run `composer update --dry-run` for get all necessary install, update or remove actions.
* run security-checker for get all library actualy installed with know security issue.

The output can is a `yaml` file or DBAL table.

## Requirements

* [composer](https://getcomposer.org/)
* [php 5.6+](http://php.net)
* [security-checker](https://security.sensiolabs.org/)

## Install

* Clone git repository or download tarball.
* install dependency by `composer install -o`

## Configure

Add an file `cua.yml` at root cua folder.

Put content:

```
composer_path: /usr/local/bin/composer # the path to composer executable
security_checker_path: /usr/bin/security-checker # The path to security-checker executable
                                                 # (set 'internal' if you want use the Internal Security Checker)
persistance:
    format: DbalPersistance # Persistence
    parameters:
        dbname: 'deps'                      # DBAL Database name
        user: 'root'                        # DBAL database username
        password: 'root'                    # DBALbatabase user password
        host: 'localhost'                   # DBAL server name or ip
        driver: 'pdo_mysql'                 # DBAL Driver name
        table_name: 'dependencies'          # the table name for DBAL persistance
        table_name_security: 'security'     # the table name for DBAL persistance

        path: ./all.yml                     # for YamlFile only
        path_security: ./all_security.yml   # for YamlFile only

project_provider:
    # type: redmine
    # parameters:
    #     dbname: 'redmine'
    #     user: 'db_user'
    #     password: '*******'
    #     host: 'localhost'
    #     driver: 'pdo_mysql'
    #     table_name: 'cua_settings'

    type: file
    parameters:
        path: projects.yml # Location of the file project list from root cua install
```

Add an file `project.yml` ar root cua folder.

Put content:

```
projects:
    'project_name':
        path: /path/to/project
        check_dependencies: true   # Optional, by default : true
        lock_path: ./composer.lock # Optional, by default : ./composer.lock. Set the location of composer.lock file from project path.
        check_security: false      # Optional, by default : false. Enable this project for command security
        php_path: php7.2           # Optional, by default : php. The php executable name (or path) for run security-checker and composer
```


### Usign with Redmine Cua Plugin

Set the format to `RedmineCuaPersistance` and `table_name` to `cua_dependencies`

In this case, the `project_name` set into the config file do same the project identifier from redmine.


### DBAL Table creation

If you use MySQL for persist, you can use the file `Sql/Create_Table.sql` for create table in your database.


## Check project configuration

open console, go to cua root folder and type :

```
php ./cua project:list
```

This command print a table with all project configured and details.

## Usage for check dependencies

open console, go to cua root folder and type :

```
php ./cua check
```

This command launch the process for all setting project and store in persistance.

## Usage for check security

open console, go to cua root folder and type :

```
php ./cua security
```

This command launch the process for all setting project and store in persistance.



# Contribute

If you whant contribute, please fork my repo, add feature or fix bugs and create new pull request.


# Todo

[ ] Add script for create table on other Database (postgre, mssql, etc.).

[ ] Get PHP and extentions requirements by project.

