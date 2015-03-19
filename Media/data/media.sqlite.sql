--
-- Media Module SQLite Database for Phire CMS 2.0
--

--  --------------------------------------------------------

--
-- Set database encoding
--

PRAGMA encoding = "UTF-8";
PRAGMA foreign_keys = ON;

-- --------------------------------------------------------
--
-- Table structure for table "media_libraries"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]media_libraries" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "name" varchar NOT NULL,
  "folder" varchar NOT NULL,
  "allowed_types" text,
  "disallowed_types" text,
  "max_filesize" integer,
  "actions" text,
  "adapter" varchar,
  "order" integer,
  PRIMARY KEY ("id")
) ;

INSERT INTO "sqlite_sequence" ("name", "seq") VALUES ('[{prefix}]media_libraries', 30000);

-- --------------------------------------------------------

--
-- Table structure for table "media"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]media" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "library_id" integer NOT NULL,
  "title" varchar NOT NULL,
  "file" varchar NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_media_library" FOREIGN KEY ("library_id") REFERENCES "[{prefix}]media_libraries" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

INSERT INTO "sqlite_sequence" ("name", "seq") VALUES ('[{prefix}]media', 31000);

-- --------------------------------------------------------