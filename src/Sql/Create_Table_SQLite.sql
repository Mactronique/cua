CREATE TABLE "dependencies" (
	"id"	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	"project"	TEXT NOT NULL,
	"library"	TEXT NOT NULL,
	"version"	TEXT NOT NULL,
	"state"	TEXT NOT NULL,
	"to_library"	TEXT,
	"to_version"	TEXT,
	"deprecated"	INTEGER,
	"updated_at"	TEXT NOT NULL
);


CREATE TABLE "security" (
	"id"	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	"projet"	TEXT NOT NULL,
	"library"	TEXT NOT NULL,
	"version"	TEXT NOT NULL,
	"state"	TEXT NOT NULL,
	"details"	TEXT NOT NULL,
	"updated_at"	TEXT NOT NULL
);

CREATE TABLE "projects" (
	"code"	TEXT NOT NULL,
	"name"	TEXT NOT NULL,
	"path"	TEXT NOT NULL,
	"lock_path"	TEXT NOT NULL,
	"php_path"	TEXT NOT NULL,
	"private_dependencies"	TEXT,
	"private_dependencies_strategy"	TEXT DEFAULT 'remove',
	"check_dependencies"	INTEGER,
	"check_security"	INTEGER,
	"updated_at"	TEXT NOT NULL,
	PRIMARY KEY("code")
);
