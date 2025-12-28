SELECT
	name,
	desc,
	reviewed,
	state,
	tags
FROM
	formulas
WHERE
	reviewed = 0
ORDER BY
	name;
