<!-- 
  NOTE : explicitly set fragments in the markdown titles may be linked
         from other places in the app (search for `learnmore#` in the code).
-->

## Introduction

**Kanji Koohii** is a website and community dedicated to help you complete the kanji learning method called _Remembering the Kanji_.

- Edit and share **kanji mnemonics** (called "stories")
- Improve long term memory with **kanji flashcards** and a spaced repetition system (SRS)
- **Track your progress** to stay motivated
- Learn words based on your kanji knowledge with the **smart dictionary**

## About the RTK book {#rtk}

<div class="ko-DocMain-whatisrtk ko-Box pt-6 no-gutter-xs-sm mb-8" markdown="1">

  <div class="ko-RtkBook float-left mr-6 mb-4 md:px-4">
    <img src="/images/3.0/help/rtk-book-cover.gif" width="137" height="205" />
  </div>

**Remembering the Kanji** (RTK) teaches you how to break down the complex kanji in smaller, simple parts that can be memorized with a _mnemonic_ system (we call it _stories_).

You can already start studying 294 kanji in 12 lessons with [**the free sample chapter**](https://web.archive.org/web/20190101104438/https://nirc.nanzan-u.ac.jp/en/files/2012/12/RK-1-6th-edition-sample.pdf) made available by the publisher.

You can buy the book on [Amazon](https://www.amazon.com/Remembering-Kanji-Complete-Japanese-Characters/dp/0824835921).<br>
[Here is a good review](https://web.archive.org/web/20210126123639/https://www.kanjiclinic.com/reviewheisigwiig.htm) from KanjiClinic.

</div>

## Studying

The Study area is where you begin your kanji journey! Start with the character ["one"](/study/kanji/1). Edit your mnemonic (also called a "story") or use one shared by the community. Then add a flashcard with the button in the top right. When you are done studying a few characters go to the [Spaced Repetition System (SRS)](/main) and click the blue pile to review new cards.

<img class="img-block img-responsive" src="/koohii/help/help-study-edit-story.png" />

If you choose to publicly share your story, it will appear in the list below. You can vote for stories that work well, copy a story from another member (you can use it as is, or you may want to edit it).

**Hints**

- If you don't edit a story, the first one that you **star** will be shown during flashcard reviews when you use the Story shortcut

### Edit Stories

#### Formatting Stories

**Keywords** are automatically formatted if the text in your story matches the keyword. If the keyword is spelled in a different way, or you use a custom keyword, surround the keyword with hashes `#like this#`.

**Primitives** can be _italicized_ in your stories by enclosing them with the star character `*like this*`.

**Links between kanji** can be created in stories by enclosing a frame number or kanji with braces. For example `{113}` or `{山}`. The decimal unicode value of a kanji can also be used for example `{40701}` will substitute into [黽](/study/kanji/黽).

#### Note About Copyright

Please remember that the original stories appearing in the RTK book are copyrighted, and **SHOULD NOT BE REPRODUCED** on the website! (you can of course enter them for your own use, as long as they are private).

### Edit Keywords

Sometimes you may want to customize one of Heisig's keywords. You may want to translate them into a non-english language.

You can do so by clicking the keyword above your story to customize it!

_Please note at this time the Study page search box is basic, and will not match custom keywords._

You can edit many keywords ate once, and review your changes in **Manage Flashcards > Edit Keywords**.

### The Dictionary {#dictionary}

**Kanji Koohii features a simple, yet smart dictionary. The Koohii dictionary is aware of your kanji knowledge: as you progress and add flashcards the dictionary will highlight vocabulary entries that are made of only those known kanji.**

Currently _known kanji_ means a kanji for which the user added a flashcard. This simple requirement is the most flexible for various needs. New cards will be learned soon, and failed cards are meant to be re-studied eventually so this works out.

<img class="img-block img-responsive" src="/koohii/help/help-dict-example-21.png" />

In the screenshot above the user has studied kanji #1 to #50. The second word "合唱" is not highlighted yet, since
the user has not yet learned 合 which is kanji #269 and will be seen later. Yet on kanji 21 already the dictionary can highlight a vocabulary entry (the verb "唱える") which is based on the user's previous knowledge.

This works in Flashcard Review too! So as you do reviews over weeks and months, and you open the dictionary you may discover new words which weren't highlighted before.

#### Dictionary Sources {#dictionary-sources}

Kanji Koohii's dictionary uses Jim Breen's [JMdict/EDICT](https://www.edrdg.org/wiki/index.php/JMdict-EDICT_Dictionary_Project) Japanese-English dictionary (see acknowlegments in the [About](/about) page).

**Koohii's dictionary only includes "priority" entries, approx. 16000 of the most common words**. Specifically: <samp>ichi1, news1, spec1, gai1</samp> of JMdict's [word priority markings](https://www.edrdg.org/wiki/index.php/JMdict-EDICT_Dictionary_Project#Word_Priority_Marking).

This is done in order to provide meaningful results for Koohii users. The dictionary would otherwise show rare, archaic and obsolete words as well as place names from a total of 170,000+ entries which you'll probably never use even after being fluent in Japanese.

As such, Koohii's dictionary is not an exhaustive reference. There are already excellent resources dedicated to this such as jisho.org. The goal for Koohii's dictionary is to help you stay focused while getting through the 2000+ common use kanji.

### Restudy List {#restudy-list}

The [Restudy List](/study/failedlist) page lets you see all the kanji that are currently in your failed cards pile (the red pile on the SRS bar chart).

From here you can begin the "Restudy" process, which has two advantages:

1. helps you navigate the Restudy List, so you don't have to manually search for each kanji
2. lets you mark specific kanji as "learned", and then review just those

You can also select <span class="ko-ExBtn ko-ExBtn--danger">Review All</span> to start a SRS review of all the kanji in your failed pile.

#### Begin Restudy

Select <span class="ko-ExBtn ko-ExBtn--danger">Begin Restudy</span> to restudy, and review, parts or all of your forgotten kanji.

- After you are done re-learning a kanji (perhaps updating your story, or using another one), select <span class="ko-ExBtn ko-ExBtn--success">Add to learned list</span> below the story.
- Each time you mark a kanji as "learned" this way, you will move to the next kanji in your Restudy List, _in Heisig index order_.
- You can go back to the Restudy List page at any time and you will see the LEARNED kanji marked in the list. Select "Begin Restudy" again to continue with the remaining kanji.

**Tip!** _If you want to re-study only specific kanji in no particular order, simply click them in the Restudy List to navigate to those Study pages (and select "Add to learned list" each time)._

#### Learned Kanji

Use the Restudy process described above, to add kanji to the Learned Kanji list. "LEARNED" kanji will remain in this state until you either review the kanji succesfully (only SRS reviews), or manually clear the list.

Select <span class="ko-ExBtn ko-ExBtn--success">Review Learned</span> to start a SRS review of only those forgotten kanji that you have marked as learned. This effectively allows you to review only parts of your forgotten kanji. You may want to leave some difficult kanji in this pile indefinitely for example - to restudy only when you'll need them.

Select <span class="ko-ExBtn ko-ExBtn--danger">Clear learned list</span> to "reset" the learned kanji. You will rarely need this, but perhaps if you took a long break, and you had some "learned" kanji left over you may want to start the re-study process again.
  
At the end of this review (in fact, _any SRS review_), forgotten kanji will remain in the failed pile but please note _they will be cleared from the learned list_. Kanji that were succesfully reviewed (including "Hard" answer) are no longer in the Restudy List - well done!

## Using Flashcards

### Adding Flashcards

Flashcards are added in **Flashcards > Manage Cards**.

If you study with _Remembering the Kanji_ you can simply enter the maximum frame number that you have studied so far.

<img class="img-block img-responsive" src="/koohii/help/help-add-order.png" />

If you are not studying the kanji in the RTK sequence (say, JLPT), or you want to skip parts of RTK, then you will need to use Add Cards > Custom Selection instead.

<img class="img-block img-responsive" src="/koohii/help/help-add-custom.png" />

## Reviewing

### Custom Review {#custom-review}

Custom Review modes let you review at any time, without managing flashcards.

In the [Custom Review](/review/custom) section, you can repeat reviews for any RTK lesson, or any range of kanji (using the RTK index numbers).

To keep track of your progress along the *Remembering the Kanji* book, you'll need to add flashcards. A flashcard marks the kanji as "learned".

**Custom Review** is probably easier and simpler to use when you begin RTK. The early kanji are simpler to remember and to write, so it is fairly easy to review even a hundred kanji a day.

However once you have learned a few hundred kanji you may find it difficult to review all of them every day. At this point you may take advantage of the **Spaced Repetition System** described in the next section.


### Spaced Repetition {#srs}

Kanji Koohii uses a **Spaced Repetition System** (also known as "SRS") based on the popular [Leitner System](http://en.wikipedia.org/wiki/Leitner_system):

> In the Leitner system, flashcards are sorted into groups according to how well you know each one in the Leitner's learning box. This is how it works: you try to recall the solution written on a flashcard. If you succeed, you send the card to the next group. But if you fail, you send it back to the first group. Each succeeding group has a longer period of time before you are required to revisit the cards.

The Leitner System helps you to:

- Make sure that you review all the information that you have learned.
- Review at increasingly longer intervals to stimulate long term memory.
- Review more of the difficult flashcards, and less of those that you know well.

### Review Bar Chart

The SRS bar chart represents stacks of flashcards. Stacks are shown from left to right. Stacks on the left side are relatively new. With each review, a card will move towards the right, representing a better knowledge.

<img class="img-block img-responsive" src="/koohii/help/help-review-srs.png" />

The first box contains <span class="clr-srs-fail">**forgotten cards**</span> in red, and <span class="clr-srs-new">**new cards**</span> in blue. You can select the blue pile or use the button above, both will take you to a review of new cards.

The boxes labelled "1", "2", etc. represents cards that have been reviewed succesfully that many times _in a row_. Therefore the higher boxes represent better knowledge!

Each of the review boxes comes in two piles of cards: <span class="clr-srs-due">**due cards**</span> in orange (ready to review), and <span class="clr-srs-undue">**undue cards**</span> in green (scheduled for review later).

#### Review Chart Colors

<div class="ko-DocMain-stacks no-gutter-xs-sm" markdown="1">

  **Cards in the first box (labelled "Fail & New")**

  <dl>
  <dt>
    <div class="ko-SrsIso is-fail"><em class="is-top"></em><em class="is-side"></em></div>
  </dt>
  <dd><strong>Failed cards.</strong> The red stack shows cards which have not been answered correctly.
      The kanji in this stack likely needs more work on the stories/mnemonics.
  </dd>
  <dt>
    <div class="ko-SrsIso is-new"><em class="is-top"></em><em class="is-side"></em></div>
  </dt>
  <dd><strong>Untested cards.</strong>
    The blue stack shows cards that have not been tested yet.
    Below the graph there is a blue link, clicking the blue link is the same as clicking the blue stack.
    The blue link simply gives you more detail, it tells you which was the latest pack of cards that 
    were added, when they were added, and how many cards remain in that pack of cards.
    Each time you add new cards, they go to the top of the blue stack.
    When you click the blue stack you get to review the most recently added cards first.
  </dd>
  </dl>

  **Cards in the review boxes**

  <dl>
  <dt>
    <div class="ko-SrsIso is-due"><em class="is-top"></em><em class="is-side"></em></div>
  </dt>
  <dd><strong>Due cards.</strong>
    (orange piles) are cards that are ready for review and need your attention. You should generally use the main button above the graph to review all due cards. However some people prefer to review from the right to the left, by selecting the orange piles directly. This lets you focus first on cards you know well, working your way down to the cards you added more recently and may be more difficult to remember. Use whichever system you prefer but we recommend to avoid this method until you are experienced with the SRS. Keep in mind all orange cards are sorted by due date, so you will always get the more "urgent" reviews first (with a small amount of shuffling).
  </dd>
  <dt>
    <div class="ko-SrsIso is-undue"><em class="is-top"></em><em class="is-side"></em></div>
  </dt>
  <dd markdown="1">**Scheduled cards**
(green piles) are scheduled for review, but have not expired yet.
In other words, they are still 'fresh' in your memory, and the SRS estimates that these don't need
your attention yet. These cards will eventually become *due* at which point you can review them.

Reviewing cards ahead of time would defeat the purpose of the SRS, which tries to stimulate your
long term memory, therefore it is not possible to select the green piles. Often times users who are new to the SRS want to do many reviews of new kanji. You can do so in the [Custom Review](/review/custom) page. These reviews won't affect the SRS and can be repeated as many as you like.

  </dd>
  </dl>

</div>

### Review Session

Clicking any of the stacks in the Leitner graph will take you to the reviewing screen :

<img class="img-block img-responsive" src="/images/2.0/learnmore/review-flashcard-rtk2.png" />

Depending on how many cards are in the stack the reviewing session could be very short or very long. Keep in mind that you can test as many or as few cards as you like, and you may leave the Review screen whenever you want!

Every time you answer a card, that card's status is updated. When you click the "Finish" button to skip to the Review Summary screen, the remaining cards that were not reviewed simply stay in the stack, and can be tested when you have more time.

When you test one of the expired stacks (orange), you get cards in order of their expiry date, starting with the least recently expired ones, i.e. first come the cards that expired first.

When you test the untested stack, it works the other way round. Cards that were the most recently added, get tested first. This lets you review immediately newly added cards, regardless of how many untested cards were already on the stack.

Cards are always shuffled when they were added or expired on the same date. In other words, during review you get the cards in the order explained above, and within this order, groups of cards that fall on the same date get shuffled together.

Reviewing is done from the keyword to the character, and not the other way around. As recommended in James Heisig's method, you should write down the characters while reviewing. Since the book teaches you the stroke order of all the components of the Japanese characters, being able to recall the kanji from the keyword means you are able to write every one of the kanji from memory. There is no planned support for testing kanji the other way round (there is however some sight-reading test/games planned).

Write down the character on a sheet of paper, or trace it in the palm of your hand, then press the <kbd>Spacebar</kbd> key or click "Flip Card" to verify your answer :

<img class="img-block img-responsive" src="/images/2.0/learnmore/review-editstory.gif" />

Note that you can edit a story <kbd>S</kbd> during a review, and even the keyword (click the keyword).

If you were correct, answer "Yes" otherwise answer "No". You can answer by clicking the buttons or using the <kbd>Y</kbd>, <kbd>N</kbd> and <kbd>E</kbd> keys.

Correctly answered cards will be promoted to the next card box, incorrectly answered cards will return to the red stack in box one. It is highly suggested that you do not settle for half answers, if you forgot even just a small part of the writing of the character, answer "No". You are your own judge, but keep in mind that it is is not a race. Also realise that because many kanji look similar, forgetting "just one small stroke" here or there can make the difference between one kanji and another.

The "Stats" panel shows you how many kanji you have been testing in this session so far, how many were answered correctly, and how many were answered incorrectly.

#### Card Ratings {#rating}

- <span markdown="true"><div class="uiIBtn uiIBtnDefault uiIBtnRed uiFcBtnAN"><span>No</span></div></span> : send card back to the restudy pile. You can work through your failed kanji later by using the [restudy feature](#restudy-list). Or, you can also directly review the Restudy pile at a later time, by clicking the red button in the [Restudy List](/study/failedlist).

- <span markdown="true"><div class="uiIBtn uiIBtnDefault uiFcBtnAG"><span>Again</span></div></span> : moves the card to the end of the review pile, and lets you repeat it _in the same review session_. Use _Again_ rating the same way you would use No : you were not able to recall the kanji. However instead of sending the card to the Restudy pile, you will review it again at the end of this session.

  The result is the same as answering No, followed by Hard/Yes/Easy : the card will be "reset" to the 1+ review box with 1-6 days interval depending if you used Hard, Yes or Easy.

  If there are any cards you rated _Again_ that are left in the pile, and you End the review prematurely (End button) these cards will go to the Restudy pile (rated as a No).

  _Again_ is best used as a kind of **learning stage** when you are reviewing the New or Restudy piles, effectively allowing you to repeat initial reviews as many times as you want while tweaking your kanji story/mnemonic.

  Keep in mind using _Again_ a lot, even for new cards, can make your reviews feel very long! Try to use **the Hard rating** with New & Restudy cards for a fixed 1 day interval without increasing the length of your review, and be careful of over-using rote memorization as that defeats the purpose of the RTK method.

- <span markdown="true"><div class="uiIBtn uiIBtnDefault uiIBtnOrange"><span>Hard</span></div></span> : demotes a card to a lower pile. The next review is scheduled at a lower interval according to the scheduling (see below).

  The "Hard" rating is very useful if you prefer not to use the Study page > Relearn > Review cycle. Even when your story is not effective, you can edit it from the Review page (shortcut <kbd>S</kbd>), and answer "Hard". That way you never have to deal with the restudy pile.

  **Tip #1**! The "Hard" rating has a _special behaviour for the first review_ (typically, cards from the blue and red piles): a "Hard" rating will _always_ set a fixed +1 day interval (whereas "Yes" would be 2-4 days). Use this when you are learning new cards, if you'd like to do additional reviews on consecutive days. Once you are more confident, rate "Yes" or "Easy".

  **Tip #2**! You can set _"Maximum box for cards marked 'Hard'"_ in Account Settings > Spaced Repetition. You can make sure for example that Hard Rating will never have an interval of more than a month. Alternatively you can set it to "1". This will cause a Hard answer to "reset" the card to the first review box, _and_ ; because of the special behaviour described above ; you will also get a review in +1 day always. You may prefer this over using "Again", if you do not want to make the review longer (instead of repeating the card, it will be scheduled tomorrow, for as long as you rate this card "Hard").

- <span markdown="true"><div class="uiIBtn uiIBtnDefault uiFcBtnAY"><span>Yes</span></div></span> : use "Yes" to promote a card to the next box, it will be scheduled for review at a longer interval. _Only use this rating if you completely remember the kanji, otherwise you should use "Hard" or "No"._

- <span markdown="true"><div class="uiIBtn uiIBtnDefault uiFcBtnAE"><span>Easy</span></div></span> : rating a card "Easy" increases the interval by 50% compared to the "Yes" answer. This is best used on cards that are easy to remember, such as very common kanji. Using "Easy" on the long run will reduce the amount of daily reviews.

**When a card is not answered correctly it will move back to the first compartment!** This is why you can gauge your current level of knowledge just by looking at the count of cards in each compartment : cards in the last compartment have not only been tested four times or more, they also have passed the test at least four times _in a row_. Thus, the cards in the last compartments correspond to the kanji you know best.

### Review Summary

Once you have completed a review (or when you click the "End" button), you will be taken to the **Review Summary** screen :

<img class="img-block img-responsive" src="/koohii/help/help-review-summary-cards.png" />

The Review Summary lists the kanji that were not answered correctly during the review session.

The table can be sorted on any column by clicking on the column headers. In the example image above the review summary is sorted on the frame numbers.

Clicking any of the keywords will take you to the corresponding character in the Study area, where you can check your mnemonics, adapt them, or maybe use a mnemonic shared by another member if yours wasn't working so well.


## Account Settings

### Spaced Repetition {#settings-srs}

You can customize the Spaced Repetition scheduling in _Account Settings_ menu:

- **Number of boxes** for successive reviews corresponds to the intervals for positive reviews and what the _maximum_ interval will be
- **Review interval multiplier** determines the spacing between reviews (in days). Consider using a smaller multiplier when you start out, if you'd like to get more reviews. When you complete RTK, consider increasing the multiplier to reduce the review load over time
- **Maximum box for cards marked 'Hard'** determines the maximum interval for a Hard answer

Here is a screenshot of the default options:

<img class="img-block img-responsive" src="/koohii/help/help-srs-options-defaults.png" />

#### Maximum interval

Cards answered "Yes" or "Easy" in the last box remain in the last box and are scheduled again at the last box's interval. To increase the maximum interval you can either increase the number of boxes, or the multiplier, or use a combination of both.

#### Variance

There is also an amount of **variance**, which adds a little "fuzziness" to the interval by moving the date a little bit backward or forward. This helps spread due cards over time, so that they don't come in big batches on the same date.

The variance factor is currently `0.15` of the interval, and there is a limit of `30` days. For example, a card scheduled in 26 days with a variance of 4 days will be scheduled anywhere from 22 to 30 days.

## FAQ

### Resetting Flashcards

First, note that _stories and kanji flashcards are separate_. With that in mind, you can simply delete all the flashcards, your stories won't be affected.

1. Go to [Remove Custom Flashcard Selection](/manage/removecustom) (from the Flashcards > Manage Flashcards menu)
2. Enter `1-3000` and click _Remove Flashcards_, then confirm
3. Now you can add cards back as you see fit, either one by one advancing through the Study pages (top right _Add Card_ buton), or add in small batches via _Manage Flashcards_ menu
