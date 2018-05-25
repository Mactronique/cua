UPGRADE FROM 1.0 to 2.0
=======================

The version 2.0 rewrite the configuration and split the file in two files.

# Rewrite the configuration

Read the `projects` part from the 1.0 cua config file do move into an new file.


This is an example to split the old v1.0 config file:

## v1.0 config file `cua.yml`

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

## Generate the v2.0 config file

v2.0 config file `cua.yml` is same as v1.0 without the `projets` configuration key:
```
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

Add the project source for use the projet YAML configuration file:
```
project_provider:
    type: file
    parameters:
        path: projects.yml # Location of the file project list from root cua install
```

## Generate the 2.0 project configuration file

v2.0 project file configuration with the v1.0 `projets` configuration key:
```
projects:
    project_name: /path/to/project
```

Il the new file, set the value of the `project name key` to the `path` key like this:

```
projects:
    project_name:
        path: /path/to/project
```
