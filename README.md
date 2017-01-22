# Kanji Koohii

[![Join the chat at https://gitter.im/kanji-koohii/Lobby](https://badges.gitter.im/kanji-koohii/Lobby.svg)](https://gitter.im/kanji-koohii/feedback?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)  _Join us on Gitter with a github account!_

**Kanji Koohii** (previously known as "Reviewing the Kanji") is a web application designed to help Japanese language learners memorize the kanji. This repository is used to track development of the website, and allow users to report bugs and make suggestions. http://kanji.koohii.com
<br>
<br>
<p align="center">
  <img src="https://raw.githubusercontent.com/fabd/kanji-koohii/master/images/kanji-koohii-desktop-preview-2017-01-14.png"><br>
  <em>The desktop view</em>
</p>
<br>
<p align="center">
  <img src="https://raw.githubusercontent.com/fabd/kanji-koohii/master/images/kanji-koohii-mobile-preview-2017-01-14.png"><br>
  <em>The mobile view</em>
</p>

## The "Staging" Website

The "staging" website is an alternate version of the website including changes in development.

You can find it at : **staging • koohii • com**

*VERY IMPORTANT: it is connected to the live database, meaning you login with your same credentials, any changes happen to your real account.*

## Feedback & Issue System

**First check NEXT issues**: [issues labelled NEXT](https://github.com/fabd/kanji-koohii/issues?q=is%3Aissue+is%3Aopen+label%3ANEXT) are what I'm working on or plan to fix soon. If it's a bug it may be already there.

**Post bugs & suggestions** on the Issues tab. Just click [New issue](https://github.com/fabd/kanji-koohii/issues/new) and voila!

**For general feedback/brainstorming** see if a topic already exists in [issues labelled DISCUSSIONS](https://github.com/fabd/kanji-koohii/issues?q=is%3Aissue+is%3Aopen+label%3Adiscussion).

**For faster iteration** join [![Join the chat at https://gitter.im/kanji-koohii/Lobby](https://badges.gitter.im/kanji-koohii/Lobby.svg)](https://gitter.im/kanji-koohii/feedback?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge). On desktop it shows a pane with all the Github repo activity.

Consider **Watching** the repository, if you often visit Github (dropdown at the top of the screen).


## Other Ways to Contribute

The website is not in a state for publishing fully on Github, yet. I created a [Open Source milestone](https://github.com/fabd/kanji-koohii/milestone/3). Not that I might add a lot more items there over time.

I am continually maintaining a codebase with code written over the past ten years. Lots of decisions I make that are not explicit in the code necessarily, because I know I will phase out this or that, or refactor some parts, at some later point. So putting up the code today on Github means I wouldn't be able to accept pull requests anyway unless it was a concerted effort.

In general there is a lot of refactoring to update the front end (Javascript) to remove the YUI 2 dependency in favor of VueJs, ES2015, and node modules. In some areas it has become almost mandatory before I can make new features. One of the most painful examples of this is the Flashcard Review page, which would be SO much easier to expand today as a VueJS component. So in general my goal is to gradually move php templating to the front end, which makes these parts much more conducive for contributions.

On the back end side, it's still using Symfony 1.x. There is also quite a bit of refactoring in places because the site was not originally written for Symfony, and still uses custom classes. For example it doesn't use Symfony's built in ORM, but a Zend Db -like API with queries that can be built programmatically but also lacking lots of advanced things from Zend Db.

With that said there are many other ways to contribute. Providing feedback, posting / updating issues, helping me test the site on mobile devices via the Gitter channel, suggest new designs (I would always make PSD templates before making significant changes anyway), etc. I will also try to make more CodePens in the future where appropriate. It's fun, and is one way to contribute (eg. [#25 3D Leitner boxes](https://github.com/fabd/kanji-koohii/issues/25)).

**Design**: the [design/PSDs folder](https://github.com/fabd/kanji-koohii/tree/master/design/PSDs)  contains resources that you can use if you want to contribute alternative better designs. Ask me if I can add something there.
