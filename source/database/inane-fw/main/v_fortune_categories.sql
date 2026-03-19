CREATE VIEW v_fortune_categories as
SELECT
	id,
	fortune,
	details->>'$.category'
from
	fortunes;

