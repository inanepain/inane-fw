CREATE TABLE elements (
	element TEXT(5)           NOT NULL
		PRIMARY KEY,
	symbol  TEXT(8),
	traits  JSON DEFAULT '[]' NOT NULL);
