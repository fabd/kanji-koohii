The `src/vite/src/assets/css/` folder contains legacy stylesheets.

Legacy .css files have been renamed to .scss since they have to be maintained or modified somehow, even though they do not follow a BEM methodology and have many stylelint issues.

In order to enable a strict stylelint config going forward, new stylesheets should be written in the `sass/` folder.

The `css/` folder is ignored by stylelint and should gradually be phased out.
