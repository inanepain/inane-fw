CREATE TABLE formulas (
	name      TEXT(50)                            NOT NULL
		PRIMARY KEY,
	desc      TEXT(200),
	version   TEXT(40),
	homepage  TEXT(200),
	installed INTEGER   DEFAULT 0                 NOT NULL,
	reviewed  INTEGER   DEFAULT 0                 NOT NULL,
	tags      TEXT(100) DEFAULT ""                NOT NULL,
	flag      INTEGER   DEFAULT 0                 NOT NULL,
	state     TEXT(10)  DEFAULT "update"          NOT NULL,
	updated   INTEGER   DEFAULT (UNIXEPOCH())     NOT NULL,
	modified  TEXT      DEFAULT CURRENT_TIMESTAMP NOT NULL);

CREATE INDEX idx_flag
	ON formulas (flag);

CREATE INDEX idx_installed
	ON formulas (installed);

CREATE INDEX idx_reviewed
	ON formulas (reviewed);

CREATE INDEX idx_tags
	ON formulas (tags);
