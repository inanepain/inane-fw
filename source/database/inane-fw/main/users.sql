CREATE TABLE users (
	id           INTEGER
		PRIMARY KEY AUTOINCREMENT,
	iddepartment INTEGER  DEFAULT 1          NOT NULL
		CONSTRAINT fk_departments
			REFERENCES departments,
	username     TEXT(15)                    NOT NULL
		CONSTRAINT uniq_usernmae
			UNIQUE,
	online       INT      DEFAULT 0          NOT NULL,
	name         TEXT(20)                    NOT NULL,
	email        TEXT(20)                    NOT NULL,
	"group"      TEXT(10) DEFAULT users      NOT NULL,
	rank         INTEGER  DEFAULT 5          NOT NULL,
	password     TEXT(50) DEFAULT "chAng3M!" NOT NULL);

CREATE INDEX idx_online
	ON users (online);

CREATE UNIQUE INDEX idx_username
	ON users (username);
