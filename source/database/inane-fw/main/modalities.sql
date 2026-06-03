CREATE TABLE modalities (
	modality    TEXT(8)           NOT NULL
		PRIMARY KEY,
	title       TEXT(20),
	description TEXT(200),
	strengths   JSON DEFAULT '[]' NOT NULL,
	challenges  JSON DEFAULT '[]' NOT NULL,
	themes      JSON DEFAULT '[]' NOT NULL,
	keywords    JSON DEFAULT '[]' NOT NULL);

CREATE INDEX idx_challenges
	ON modalities (challenges);

CREATE INDEX idx_keywords
	ON modalities (keywords);

CREATE INDEX idx_strengths
	ON modalities (strengths);

CREATE INDEX idx_themes
	ON modalities (themes);
