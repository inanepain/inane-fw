CREATE TABLE fortunes (
	id        INTEGER                           NOT NULL
		PRIMARY KEY,
	favourite boolean DEFAULT 0                 NOT NULL,
	fortune   TEXT                              NOT NULL,
	views     INTEGER DEFAULT 1                 NOT NULL,
	details   JSON    DEFAULT '{"category":[]}' NOT NULL,
	created   INTEGER DEFAULT CURRENT_TIMESTAMP NOT NULL,
	viewed    INTEGER DEFAULT (UNIXEPOCH())     NOT NULL);

CREATE INDEX idx_favourite
	ON fortunes (favourite);

CREATE INDEX idx_fortune
	ON fortunes (fortune);

