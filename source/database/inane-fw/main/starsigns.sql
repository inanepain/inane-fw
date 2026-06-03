CREATE TABLE starsigns (
	starsign TEXT(11)          NOT NULL
		PRIMARY KEY,
	symbol   TEXT(8),
	start    TEXT(10),
	end      TEXT(10),
	element  TEXT(5)
		CONSTRAINT fk_elements
			REFERENCES elements,
	modality TEXT(8)
		CONSTRAINT fk_modalities
			REFERENCES modalities,
	traits   JSON DEFAULT '[]' NOT NULL);

CREATE INDEX idx_element
	ON starsigns (element);

CREATE INDEX idx_end
	ON starsigns (end);

CREATE INDEX idx_modality
	ON starsigns (modality);

CREATE INDEX idx_start
	ON starsigns (start);

CREATE INDEX idx_traits
	ON starsigns (traits);
