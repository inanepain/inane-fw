CREATE TABLE divinations (
	id          INTEGER                            NOT NULL
		PRIMARY KEY AUTOINCREMENT,
	id_fortunes INTEGER                            NOT NULL
		CONSTRAINT fk_fortunes
			REFERENCES fortunes,
	action      TEXT(15) DEFAULT "view"            NOT NULL,
	target      TEXT(15) DEFAULT "fortune"         NOT NULL,
	created     datetime DEFAULT CURRENT_TIMESTAMP NOT NULL);

CREATE INDEX idx_action
	ON divinations (action);

CREATE INDEX idx_action_target
	ON divinations (action, target);

CREATE INDEX idx_id_fortunes
	ON divinations (id_fortunes);

CREATE INDEX idx_target
	ON divinations (target);

