# cua
Composer Update Analyser

[![Build Status](https://travis-ci.org/InExtenso/cua.svg?branch=master)](https://travis-ci.org/InExtenso/cua) [![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

This utility can read the `composer.lock` for get all library used by your project and run `composer update --dry-run` for get all necessary install, update or remove actions.

The output can is a `yaml` file or DBAL table.

## Requirements

* composer
* php 5.6+

## Install

* Clone git repository or download tarball.
* install dependency by `composer install -o`

## Configure

Create an file `cua.yml` at root cua folder.

Put content :
```
projects:
    project_name: /path/to/project
composer_path: /usr/local/bin/composer # the path to composer
persistance:
    format: DbalPersistance # Persistence
    parameters:
        dbname: 'deps' # DBAL Database name
        user: 'root' # DBAL database username
        password: 'root' # DBALbatabase user password
        host: 'localhost' # DBAL server name or ip
        driver: 'pdo_mysql' # DBAL Driver name
        table_name: 'dependencies' # the table name for DBAL persistance
        path: ./all.yml # for YamlFile only

```

### Usign with Redmine Cua Plugin

Set the format to `RedmineCuaPersistance` and `table_name` to `cua_dependencies`

In this case, the `project_name` set into the config file do same the project identifier from redmine.


### DBAL Table creation

If you use MySQL for persist, you can use the file `Sql/Create_Table.sql` for create table in your database.


## Usage

open console, go to cua root folder and type :

```
php ./cua check
```

This command launch the process for all setting project and store in persistance.


# Contribute

If you whant contribute, please fork my repo, add feature or fix bugs and create new pull request.


# Todo

[ ] Add script for create table on other Database (postgre, mssql, etc.).

[ ] Get PHP and extentions requirements by project.

