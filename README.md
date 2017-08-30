# Kanji Koohii

**Kanji Koohii** is a web application designed to help Japanese language learners remember the kanji. http://kanji.koohii.com
<br>
[![Join the chat at https://gitter.im/kanji-koohii/Lobby](https://badges.gitter.im/kanji-koohii/Lobby.svg)](https://gitter.im/kanji-koohii/feedback?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
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


## Feedback & Suggestions

Create an "issue" in the Issues tab. [New issue](https://github.com/fabd/kanji-koohii/issues/new).

## Contributing
Let's talk on [on Gitter](https://gitter.im/kanji-koohii/development)! It'll save you time getting into the codebase. If you'd like to help on existing issues have a look at [Milestones](https://github.com/fabd/kanji-koohii/milestones). I use milestones to group issues in categories such as [UX Improvements](https://github.com/fabd/kanji-koohii/issues?q=is%3Aopen+is%3Aissue+milestone%3A%22UX+Misc.+Improvements%22), [Front End Performance](https://github.com/fabd/kanji-koohii/issues?q=is%3Aopen+is%3Aissue+milestone%3A%22Front+End+Performance%22), etc.

#### Project History
As you look at the code, please keep in mind I started the website all the way back in the [August 2005](https://kanji.koohii.com/news/2005/8). The website was pretty much one-php-script-per-page for the first year. It was known then as "Reviewing the Kanji". A year or so later, I created a very barebones MVC inspired by Symfony 1. Thankfully because I followed the same API and conventions than Symfony a few years later I was able to refactor to the actual Symfony 1 package.

This is the reason why this may not look like a typical Symfony project. For example, I had my own database API largely inspired by ZendDb, called "coreDatabase". It is still used as of writing. I've never used "Doctrine" nor any of the "scaffolding" utility.

#### Requirements
Development requires a typical LAMP setup (Apache/MySQL/Php) with a shell (ie. Linux, OS X or a VM on Windows).

- **Php 7.0+** with xml, mbstring, curl.
- **OPTIONAL**: JRE (Java Runtime Env.) for Closure compiler and yuicompressor (.jar).

#### Documentation
The [documentation](https://fabd.github.io/kanji-koohii-docs/project-structure.html) is manually updated and intended to work as a cheatsheet for common MVC & custom APIs. It has been very helpful for me over the years for that purpose. For more fine-grained documentation refer to the phpdoc comments. For Koohii classes I tend to keep a summary of the methods at the top of the file.

*The documentation is a work in progress.* (I am converting the docs to an online repo using Hexo).

#### Development setup

* [First Time Setup](https://github.com/fabd/kanji-koohii/wiki/Open-Source:-First-Time-Setup) for setting up the repository and database
* [Build](https://github.com/fabd/kanji-koohii/wiki/Open-Source:-Build) for basic info about dev/test environments


## Project Roadmap

* **Refactoring** legacy php templating to Vuejs client-side components
* **Vocabulary features** : "favorite" example words in Study pages, create a vocab deck and review with SRS
* **Improve Study pages** : better moderation & voting system, stroke order animations, better search
* **Improve the API** for third party clients like [Kanji Ryokucha](http://forum.koohii.com/thread-12829.html)


For more details see [Open Source](https://github.com/fabd/kanji-koohii/wiki/Open-Source) on the wiki.


## LICENSE

[![License: AGPL v3](https://img.shields.io/badge/License-AGPL%20v3-blue.svg)](http://www.gnu.org/licenses/agpl-3.0)

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
