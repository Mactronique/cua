# cua
Composer Update Analyser

This utility can read the composer.lock for get all ligraby user by your project and run `composer update --dry-run` for get all necessary update.

The output can is a yaml file or DBAL table.

## DBAL table schema

project varchar(50)
library varchar(250)
version varchar(250)
state varchar(20)
to_library varchar(250)
to_version varchar(250)
deprecated bit

Index idx_plv (project, library, version)


