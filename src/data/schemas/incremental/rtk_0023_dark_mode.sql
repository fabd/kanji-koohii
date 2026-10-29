--
-- Date: 2026.10
--
-- Theme switcher : the user's selected color theme (cf. rtkThemes).
--

ALTER TABLE `users`
  ADD COLUMN `opt_theme` VARCHAR(16) NOT NULL DEFAULT 'classic' AFTER `opt_sequence`;
