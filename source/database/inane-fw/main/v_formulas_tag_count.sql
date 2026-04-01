CREATE VIEW "v_formulas_tag_count" AS WITH RECURSIVE split(tag, rest) AS (
  -- Seed: grab first tag from each row
  SELECT
    TRIM(SUBSTR(tags, 1, INSTR(tags || ',', ',') - 1)),
    SUBSTR(tags || ',', INSTR(tags || ',', ',') + 1)
  FROM formulas
  WHERE tags IS NOT NULL AND tags != ''

  UNION ALL

  -- Recurse: peel off the next tag
  SELECT
    TRIM(SUBSTR(rest, 1, INSTR(rest, ',') - 1)),
    SUBSTR(rest, INSTR(rest, ',') + 1)
  FROM split
  WHERE rest != ''
)
SELECT tag, COUNT(*) AS usage_count
FROM split
WHERE tag != ''
GROUP BY tag
ORDER BY usage_count DESC;
