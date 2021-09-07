# Kanji Koohii

**Kanji Koohii** is a web application designed to help Japanese language learners remember the kanji. https://kanji.koohii.com
<br>

<p align="center">
  <img src="https://raw.githubusercontent.com/fabd/kanji-koohii/master/doc/github/README - mobile.png"><br>
  <em>The mobile view</em>
</p>
<br>
<p align="center">
  <img src="https://raw.githubusercontent.com/fabd/kanji-koohii/master/doc/github/README - desktop.png"><br>
  <em>The desktop view</em>
</p>


## Bugs & Feature Requests

Bugs reports and feature requests are welcome, please create a [New issue](https://github.com/fabd/kanji-koohii/issues/new) in the Issues tab. 

## Contributing
In general **pull requests** for small fixes/typos are welcome.

For substantial changes it's probably a good idea to let me know in advance so I can tell you if I'm interested to merge and also give you useful information.

Keep in mind I started the website all the way back in the [August 2005](https://kanji.koohii.com/news/2005/8), so there are challenges that come with maintaining the site for such a long time. It was a barebones php4 site for the first year. It was later refactored to Symfony MVC. Today Koohii still runs with Symfony 1 in the backend, but the frontend can take advantage of **ViteJs, Vue 3 and Tailwind CSS**.

## Development

Installation is fairly simple with Docker CE. The MySQL (Mariadb) container includes a sample database with a selection of the top-voted kanji stories. See [Installation](doc/Installation.md) and [Database](doc/Database.md) guides.

For any questions related to the codebase, you may be able to reach me [on discord](https://discord.gg/VseqVcy3vS).

## LICENSE

[![License: AGPL v3](https://img.shields.io/badge/License-AGPL%20v3-blue.svg)](http://www.gnu.org/licenses/agpl-3.0) [![Join the chat at https://gitter.im/kanji-koohii/develop](https://badges.gitter.im/kanji-koohii/develop.svg)](https://gitter.im/kanji-koohii/develop?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

The source code is licensed as [AGPLv3](http://www.fsf.org/licensing/licenses/agpl-3.0.html) (see the LICENSE file), with exception of the following third party licenses.

### Acknowledgments

This list is not exhaustive and may be updated from time to time. The main purpose is to give an overview of things Kanji Koohii is built on.

* Symfony 1.4 (main framework)
* Zend Framework (bits and pieces)
* [Vue.js](https://vuejs.org/)
* [YUI2](http://yui.github.io/yui2/) (legacy Javascript being phased out)
* [UTF-8 to Code Point Array Converter in PHP](https://hsivonen.fi/php-utf8/) by Henri Sivonen
* FontAwesome (icons)

### Copyright Notices

**Choice of license**. The GNU AFFERO GENERAL PUBLIC LICENSE v3 is chosen to encourage cooperation, particularly in the case of network distributed software. Specifically: *"public use of a modified version, on a publicly accessible server, gives the public access to the source code of the modified version."* 

**Original assets**. The website design and identity as "Kanji Koohii", as well as original artwork (such as the logo) are Copyright 2017 Fabrice Denis and intended for use exclusively at the kanji.koohii.com domain.

**RTK Index and Keywords**: The AGPL license does not cover permission granted explicitly to Fabrice Denis and for use on Kanji Koohii (previously "Reviewing the Kanji") by James W. Heisig, author of "Remembering the Kanji", to use the RTK index and keywords. The database provided with the repository includes RTK index and keywords for development purposes only, and the permission to use them does *not* extend to derived works based on this public repository and its data files.

**Font Awesome 5 Pro**: as a backer of [FontAwesome 5 Pro](https://www.kickstarter.com/projects/232193852/font-awesome-5), I may use FA 5 Pro resources in the future. The license is for my own use on my projects including Kanji Koohii, and does not extend to any derivative uses of this software (see [FAQ](https://www.kickstarter.com/projects/232193852/font-awesome-5/faqs)).
