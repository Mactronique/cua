CREATE TABLE "dependencies" (
  "id" serial NOT NULL PRIMARY KEY,
  "project" varchar(50) NOT NULL,
  "library" varchar(250) NOT NULL,
  "version" varchar(250) NOT NULL,
  "state" varchar(20) NOT NULL,
  "to_library" varchar(250) NULL,
  "to_version" varchar(250) NULL,
  "deprecated" boolean NULL,
  "updated_at" timestamp NOT NULL
);

CREATE UNIQUE INDEX CONCURRENTLY idx_proj_lib
    ON dependencies (project, library);

ALTER TABLE dependencies
    ADD CONSTRAINT unique_proj_lib
        UNIQUE USING INDEX idx_proj_lib;

CREATE INDEX idx_plv
    ON dependencies (project, library, version);

CREATE TABLE "security" (
    "id" serial NOT NULL PRIMARY KEY,
    "project" varchar(50) NOT NULL,
    "library" varchar(250) NOT NULL,
    "version" varchar(250) NOT NULL,
    "state" varchar(20) NOT NULL,
    "details" text NOT NULL,
    "updated_at" timestamp NOT NULL
);


CREATE TABLE "projects" (
    "code" varchar(10) NOT NULL PRIMARY KEY,
    "name" varchar(100) NOT NULL,
    "path" varchar(255) NOT NULL,
    "lock_path" varchar(255) NOT NULL,
    "php_path" varchar(255) NOT NULL,
    "private_dependencies" text DEFAULT NULL,
    "private_dependencies_strategy" varchar(10) DEFAULT 'remove',
    "check_dependencies" boolean DEFAULT NULL,
    "check_security" boolean DEFAULT NULL,
    "updated_at" timestamp NOT NULL
  );
