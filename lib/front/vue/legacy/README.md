# What is this folder

Some legacy Javascript (from the YUI2 era) has been included in webpack bundles, to simplify or improve the bundles used in the app.

The new syntax such as `const` chokes the legacy `batch/lint` script (part of the legacy build system).

To avoid this, the files are moved to a new location.

So in summary:

- files are moved out of the directories scanned by batch/lint
- files are syntax checked by the webpack build instead

These files are meant to be phased out, or refactored someday/never.
