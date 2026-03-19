CREATE VIEW v_fortunes_category_usage_count as
SELECT
	categories.value,
	COUNT(fortunes.id) AS usageCount
FROM
	fortunes,
	JSON_EACH(details, '$.category') AS categories
WHERE
	JSON_VALID(details)
GROUP BY
	categories.value
ORDER BY
	usageCount DESC;

