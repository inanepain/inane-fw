CREATE VIEW v_hindex_fortunes_views as
WITH ranked AS (
	SELECT views,
	       ROW_NUMBER() OVER (ORDER BY views DESC) AS rank
	FROM fortunes
)
SELECT MAX(rank) AS h_index,
       CONCAT_WS(' ', MAX(rank), 'fortunes have', MAX(rank), 'views') as Description
FROM ranked
WHERE views >= rank;
