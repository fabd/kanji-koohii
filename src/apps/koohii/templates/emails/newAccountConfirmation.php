Hello <?= $username; ?>,

Welcome to <?= _CJ('Kanji Koohii!'); ?>!

Sign in from this page:
<?= url_for('@login', true)."\n"; ?>

Learn more about using the website and its spaced repetition system here:
<?= url_for('@learnmore', true)."\n"; ?>

Need help with your account? Please use the contact page:
<?= url_for('@contact', true)."\n"; ?>


If you didn't sign up to <?= _CJ('Kanji Koohii!'); ?>, please discard this e-mail and we won't e-mail you again.

Sincerely,
Fabrice (admin) @ <?= _CJ('Kanji Koohii!'); ?>

