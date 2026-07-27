-- 2026-07-27 — allow half-point IPCR ratings.
--
-- The rating inputs are step="0.5", but ipcr_items.rating was TINYINT, so 4.5 was
-- truncated to 4 and 0.5 collapsed to 0 — which violates CHECK (rating BETWEEN 1 AND 5)
-- and aborted the whole submit with "Server error saving form."
--
-- Widening is lossless: existing whole-number ratings are preserved as n.0.
-- Already applied to the local csu_piat_aopcr_ipcr database.

ALTER TABLE ipcr_items MODIFY rating DECIMAL(2,1) DEFAULT NULL;
