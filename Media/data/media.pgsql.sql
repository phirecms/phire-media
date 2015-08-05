--
-- Media Module PostgreSQL Database for Phire CMS 2.0
--

-- --------------------------------------------------------
--
-- Table structure for table "media_libraries"
--

CREATE SEQUENCE library_id_seq START 30001;

CREATE TABLE IF NOT EXISTS "[{prefix}]media_libraries" (
  "id" integer NOT NULL DEFAULT nextval('library_id_seq'),
  "name" varchar(255) NOT NULL,
  "folder" varchar(255) NOT NULL,
  "allowed_types" text,
  "disallowed_types" text,
  "max_filesize" integer,
  "actions" text,
  "adapter" varchar(255),
  "order" integer,
  PRIMARY KEY ("id")
) ;

ALTER SEQUENCE library_id_seq OWNED BY "[{prefix}]media_libraries"."id";

-- --------------------------------------------------------


--
-- Table structure for table "media"
--

CREATE SEQUENCE media_id_seq START 31001;

CREATE TABLE IF NOT EXISTS "[{prefix}]media" (
  "id" integer NOT NULL DEFAULT nextval('media_id_seq'),
  "library_id" integer NOT NULL,
  "title" varchar(255) NOT NULL,
  "file" varchar(255) NOT NULL,
  "size" integer NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_media_library" FOREIGN KEY ("library_id") REFERENCES "[{prefix}]media_libraries" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

ALTER SEQUENCE media_id_seq OWNED BY "[{prefix}]media"."id";

-- --------------------------------------------------------