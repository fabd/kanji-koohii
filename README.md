# Kanji Koohii

Kanji Koohii (previously known as "Reviewing the Kanki") is a web application designed to help Japanese language learners memorize the kanji.

http://kanji.koohii.com

The staging (in development, *bleeding edge*) version: **staging • koohii • com**

This repository will be used to track development of the staging website, and allow users to report bugs and make suggestions.

<p align="center">
  <img src="https://raw.githubusercontent.com/fabd/kanji-koohii/master/images/kanji-koohii-desktop-preview-2017-01-14.png"><br>
  <em>The desktop view</em>
</p>

In particular the website is undergoing a big **responsibe and mobile update**.

<p align="center">
  <img src="https://raw.githubusercontent.com/fabd/kanji-koohii/master/images/kanji-koohii-mobile-preview-2017-01-14.png"><br>
  <em>The mobile view</em>
</p>

## Contributing

Both **issues and suggestions ** can be posted in the Issues tab. Just click [New issue]() and voila! I will assign tags eventually.

If I assign the tag `someday-maybe` it means I like the idea but as a solo developer doing this in my spare time I'm just being honest and acknowledging it is not planned any time soon. That said if such an issue is seeing repeated activity, who knows. It really depends how complex it is, and whether some work can be delegated.


## Open Source... Not yet

The website is not currently open sourced for various reasons. Mainly because it has been maintained for the past ten years, it's not in a state which I believe is conducive to good collaboration due to having lots of legacy code as well as modern code (ie. YUI 2 in the front end, vs Vue JS 2 recently).

Instead, I may be able to accept contributions through components, for example if I author a Vue JS component I may upload it. Documentation also could be uploaded as a Markdown file and be community maintained. We'll see.

Eventually I'd like to be able to publish the website again to Github as I did some years ago, but it needs to be in a good state otherwise the time investment in managing the repository and updating master branch etc. is not worth it.

Here I will describe some of the challenges I have with the code base (briefly):

- started in 2006 with bare bones php 4 includes footer / header...
- refactored to a lightweight / minimal version of symfony 1
- several years later finally transitioned to the real symfony 1.x framework (at that point using symfony 2 would be a full rewrite)

However still lots of legacy code such as:

- a custom / lightweight MySQL interface modelled after Zend Db (could realistically be refactored to the real Zend Db which was the idea originally). Thus it is fairly secure AFAIK due to proper escaping everything by programmatically building queries etc. However it's not using symfony's built in ORM's and therefore makes it difficult to just add in ready made plugins for symfony
- 90% of the front end was written around 2010 when I was quite proficient with object oriented Javascript, however since then Javascript has exploded.. and a lot of the YUI 2 code would be so much easier to do nowadays with Vue JS

Good things happening recently:

- building with **webpack 2** and **babel** (since the mobile/responsive update Nov 2016- jan 2017)
- .. thus able to use ES2015, and npm modules
- **VueJS 2** is now built in (starting with the mobile side nav which is a custom VueJS instanced component) .. opening the door for much more interactive components with transitiong effects (good for mobile)
