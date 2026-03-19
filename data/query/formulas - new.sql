SELECT
	name,
	desc,
	reviewed,
	state,
	tags
FROM
	formulas
WHERE
	state = 'new'
ORDER BY
	name;
