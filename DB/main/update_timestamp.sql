CREATE TRIGGER "update_timestamp"
AFTER UPDATE
ON "formulas"
BEGIN
  UPDATE formulas
	SET updated = UNIXEPOCH(),
	modified = CURRENT_TIMESTAMP
	WHERE name = OLD.name;
END;

