## Kanji Koohii helps you remember the kanji

**Kanji Koohii** is a website and community dedicated to help you complete the kanji learning method called _Remembering the Kanji_.

*   Edit and share kanji stories with fellow learners
*   Vote for the best stories, copy the ones you like
*   Review with scheduled flashcards
*   Track your progress


## About Remembering the Kanji {#help-rtk}

<div id="whatisrtk_box" class="padded-box-inset no-gutter-xs-sm mb-2" markdown="1">

  <div class="book">
    <img src="/images/3.0/help/rtk-book-cover.gif" width="137" height="205" class="css3-ssh" />
  </div>

Remembering the Kanji (RTK) teaches you how to break down the complex kanji in smaller, simple parts that can be memorized with a _mnemonic_ system (we call it _stories_).

[**Start with the free sample chapter**](http://nirc.nanzan-u.ac.jp/en/files/2012/12/RK-1-6th-edition-sample.pdf) from the publisher (PDF). It covers 294 kanji and 12 lessons.

You can buy the book on [Amazon](https://www.amazon.com/Remembering-Kanji-Complete-Japanese-Characters/dp/0824835921) as well as [publisher](http://nirc.nanzan-u.ac.jp/publications/miscPublications/Remembering_the_Kanji_1.htm) in paper or ebook format. [Here is a good review](http://www.kanjiclinic.com/reviewheisigwiig.htm) from KanjiClinic.

</div>


## What is Spaced Repetition?

Kanji Koohii uses a _spaced repetition system_ (also known as "SRS") based on the popular [Leitner System](http://en.wikipedia.org/wiki/Leitner_system):

> In the Leitner system, flashcards are sorted into groups according to how well you know each one in the Leitner's learning box. This is how it works: you try to recall the solution written on a flashcard. If you succeed, you send the card to the next group. But if you fail, you send it back to the first group. Each succeeding group has a longer period of time before you are required to revisit the cards. -- Source: [Wikipedia](http://en.wikipedia.org/wiki/Leitner_system)

The Leitner System helps you to:

*   Make sure that you review all the information that you have learned.
*   Review at increasingly longer intervals to stimulate long term memory.
*   Review more of the difficult flashcards, and less of those that you know well.


## Adding Flashcards {#help-manage-cards}

Flashcards are added in **Flashcards > Manage Cards**.

If you study with _Remembering the Kanji_ you can simply enter the maximum frame number that you have studied so far.

<img class="img-block img-responsive" src="/koohii/__help/help-add-order.png" />

If you are not studying the kanji in the RTK sequence (say, JLPT), or you want to skip parts of RTK, then you will need to use Add Cards > Custom Selection instead.

<img class="img-block img-responsive" src="/koohii/__help/help-add-custom.png" />


## The Spaced Repetition System (SRS) {#help-srs}

The SRS bar chart represents stacks of flashcards. Stacks are shown from left to right. Stacks on the left side are relatively new. With each review, a card will move towards the right, representing a better knowledge.

<img class="img-block img-responsive" src="/koohii/__help/help-review-srs.png" />

The first box contains <span style="color:#f16232;font-weight:bold;">forgotten cards</span> in red, and <span style="color:rgb(64, 168, 229);font-weight:bold;">new cards</span> in blue. You can select the blue pile or use the button above, both will take you to a review of new cards.

The following boxes represent a level of knowledge: cards have been reviewed one or more times succesfully. Each box comes with two piles of cards: <span style="color:#f7a247;font-weight:bold;">due cards</span> in orange, and <span style="color:#72c569;font-weight:bold;">scheduled cards</span> in green.

<div class="stacks_legend" markdown="1">

  ### Stacks in the first compartment

  <dl>
  <dt class="failed">&nbsp;</dt>
  <dd><strong>Failed cards.</strong> The red stack shows cards which have not been answered correctly.
      The kanji in this stack likely needs more work on the stories/mnemonics.
  </dd>
  <dt class="untested">&nbsp;</dt>
  <dd><strong>Untested cards.</strong>
    The blue stack shows cards that have not been tested yet.
    Below the graph there is a blue link, clicking the blue link is the same as clicking the blue stack.
    The blue link simply gives you more detail, it tells you which was the latest pack of cards that 
    were added, when they were added, and how many cards remain in that pack of cards.
    Each time you add new cards, they go to the top of the blue stack.
    When you click the blue stack you get to review the most recently added cards first.
  </dd>
  </dl>

  ### Stacks in the other compartments

  <dl>
  <dt class="expired">&nbsp;</dt>
  <dd><strong>Due cards.</strong>
    (orange piles) are cards that are ready for review and need your attention. You should generally use the main button above the graph to review all due cards. However some people prefer to review from the right to the left, by selecting the orange piles directly. This lets you focus first on cards you know well, working your way down to the cards you added more recently and may be more difficult to remember. Use whichever system you prefer but we recommend to avoid this method until you are experienced with the SRS. Keep in mind all orange cards are sorted by due date, so you will always get the more "urgent" reviews first (with a small amount of shuffling).
  </dd>
  <dt class="unexpired">&nbsp;</dt>
  <dd markdown="1">**Scheduled cards**
(green piles) are scheduled for review, but have not expired yet.
In other words, they are still 'fresh' in your memory, and the SRS estimates that these don't need
your attention yet. These cards will eventually become *due* at which point you can review them.

Reviewing cards ahead of time would defeat the purpose of the SRS, which tries to stimulate your
long term memory, therefore it is not possible to select the green piles. Often times users who are new to the SRS want to do many reviews of new kanji. You can do so in the [Kanji Review](/review/custom) page. These reviews won't affect the SRS and can be repeated as many as you like.
  </dd>
  </dl>

</div>

A card that is answered correctly will be promoted to the next compartment. Since it also gets scheduled for review, it will also always move to the green stack.

**When a card is not answered correctly it will move back to the first compartment!** This is why you can gauge your current level of knowledge just by looking at the count of cards in each compartment : cards in the last compartment have not only been tested four times or more, they also have passed the test at least four times _in a row_. Thus, the cards in the last compartments correspond to the kanji you know best.


## Scheduling {#help-scheduling}

When a card has been tested, it is scheduled for review in a number of days corresponding to which compartment it is moving to :

| Cards moving to compartment... | Are scheduled for review in... |
| - | ---------------------- |
| 1 | 0 days (incorrectly answered cards)
| 2 | 3 days
| 3 | 7 days
| 4 | 14 days
| 5 | 30 days
| 6 | 60 days
| 7 | 120 days
| 8 * | 240 days

**\*** : cards tested succesfully in the last box remain in the last box and are scheduled again at the maximum time interval.  

There is also an amount of _variance_ added to the interval to help shuffle the flashcards over time. It is roughly one sixth of the interval so for example, a card going on a 30 day interval may be scheduled anywhere from 25 days to 35 days.  


## Reviewing {#help-reviewing}

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

If you were correct, answer "Yes" otherwise answer "No". Answering "Easy" will increase the interval by 50% compared to the "Yes" answer. You can answer by clicking the buttons or using the <kbd>Y</kbd>, <kbd>N</kbd> and <kbd>E</kbd> keys.

Correctly answered cards will be promoted to the next card box, incorrectly answered cards will return to the red stack in box one. It is highly suggested that you do not settle for half answers, if you forgot even just a small part of the writing of the character, answer "No". You are your own judge, but keep in mind that it is is not a race. Also realise that because many kanji look similar, forgetting "just one small stroke" here or there can make the difference between one kanji and another.

The "Stats" panel shows you how many kanji you have been testing in this session so far, how many were answered correctly, and how many were answered incorrectly.

At the end of the session, or when you click the "Skip to summary" button, you will be taken to the **Review Summary** screen :

<img class="img-block img-responsive" src="/koohii/__help/help-review-summary-cards.png" />

The Review Summary lists the kanji that were not answered correctly during the review session.

The table can be sorted on any column by clicking on the column headers. In the example image above the review summary is sorted on the frame numbers.

Clicking any of the keywords will take you to the corresponding character in the Study area, where you can check your mnemonics, adapt them, or maybe use a mnemonic shared by another member if yours wasn't working so well.


## Study And Share Stories

The Study area is the most active area of the website, after the flashcard reviews : this is where you can enter your stories (as per Remembering the Kanji's method) and share them with other members :

<img class="img-block img-responsive" src="/koohii/__help/help-study-edit-story.png" />

Here too, you can customize the keyword simply by clicking it.

There are two ways to enter the Study area : click the "Study" link in the main navigation bar, which will show you an introductory text with some hints for editing your stories. The second way is when you click the red stack representing your "failed" flashcards, this gives you the opportunity to rework stories that didn't work well, see what new ideas have been shared by other members, and eventually click the "Learned" button to move the flashcard back into the review cycle.

If you choose to publicly share your story, it will appear in the list below. You can vote for stories that work well, copy a story from another member (you can use it as is, or you may want to edit it).




## Benefits {#help-benefits}

*   With the Leitner system, each cardbox represents a level of knowledge of the kanji. You can get a rapid estimate of your current progress simply by checking how many cards are in each box.
*   You are able to set your own priorities simply by choosing the card box you want to work on. If you feel tired or you don't have enough time, review the higher compartments. If you are ready to tackle difficult kanji, work on the lower compartments.
*   Too many reviews in a short period is a waste of time, as the information learned will remain in short term memory. Wait too long before reviewing, and you have lost the information. The scheduling system in "Kanji Koohii!" uses increasingly longer spaced reviews, in order to promote long-term memory retention.
*   You can optimize your reviewing time thanks to the scheduling system. There will be lots of reviews early on, but once your cards spread into the higher compartments, they will be scheduled for longer intervals, during which you can focus on the kanji that needs more attention.

## FAQ  {#help-faq}

### I can not see Japanese characters in my browser.
You have to enable East Asian languages support in Windows in order to see the kanji correctly. See [Installing Japanese Support](http://greggman.com/japan/xp-ime/xp-ime.htm) for a detailed how-to on installing East Asian language characters and the Input Method Editor (IME) which lets you type in Japanese.
