SELECT
	name,
	desc,
-- 	reviewed,
-- 	state,
	tags
FROM
	formulas
WHERE
	installed = 1
ORDER BY
	name;
