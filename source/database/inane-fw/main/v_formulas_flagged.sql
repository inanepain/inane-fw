CREATE VIEW "v_formulas_flagged" AS SELECT
	*,
	rowid AS NAVICAT_ROWID
FROM
	formulas
WHERE
	flag = '1';
