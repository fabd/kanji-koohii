# Kanji Koohii API

## Overview
The API is currently in **development**.

## Terms of Use
You agree to the terms below when using the API and the API key. [Contact Fabrice](http://kanji.koohii.com/contact) for questions/suggestions.

**The goal of the API is to allow Kanji Koohii users to review more efficiently on mobile devices and tablets, and overall improve their mobile experience**. This includes potentially offline functionality. An app may also go beyond the website functionality by providing all kinds of lookups and touch based features.

What the API is NOT designed for, is to provide a complete replacement for the website and drive users away from the Kanji Koohii website. That much should be obvious. Keep in mind my site is relatively small. I have no funding. The financial support coming from donations and a few advertisers is very important to keep me going. So it is important that the app is an extension of the website. It is fine if the users do all of their reviews on the app, and that is the main scenario for the API. What is NOT agreeable to me is for an app to feature multiple Kanji Koohii stories per character, thus greatly reducing the incentive for new users to visit Kanji Koohii.

* For sign ups (new accounts): the user is sent to the website in a web view, or separate browser app. Automated creation of account is not allowed. The user must visit the site to create his account. That said, you can still have some functionality that requires no account and let the user join his Koohii account later.
* Login of course can be automated transparently for the user with their provided credentials.
* By creating an app (desktop, mobile, any kind) that connects to the Kanji Koohii API you agree that your app does NOT feature stories scraped from the website. Please use "top voted" stories provided by [kanji-koohii-files](https://github.com/fabd/kanji-koohii-files) sample database. This applies to other kanji information as well. Please don't waste server resources: use KANJIDIC2, JMDICT, etc. If you need some data like 5th/6th edition RTK indexes, just ask.

#### Performance considerations

* To avoid stressing the server (keep in mind we're not Facebook or Twitter and my site is not using expensive servers!) as a general rule of thumb you should always use a minimum of one second between any API http requests. Note that mass updates such as updating a bunch of flashcards usually take an array of data, so it is not necessary, and should be avoided as much as possible to loop over singular items and make a request for each. Some large scale websites have APIs that work like this, not mine. Caching is pretty basic and many small requests is inefficient.
Paid apps and in-app advertising are OK as long as requirements are met.

## Obtaining your API key

Currently the API is in testing and available for public consumption with `api_key=TESTING`.


## API Usage

#### Glossary

**UCS codes** : Kanji Koohii uses the codes from the [Universal Coded Character Set](https://en.wikipedia.org/wiki/Universal_Coded_Character_Set) to uniquely identify kanji and their corresponding flashcards. This allows to refer to kanji independentily of the Heisig index, which sadly has changed between editions of the RTK books. The website uses UCS-2 codes (16 bit value) as unique keys. This covers all but the rarest forms of Japanese and Chinese characters. All Japanese characters in the range 0x4e00 - 0x9faf which are also documented in KANJIDIC are supported. See [Unicode Kanji Code Table](http://www.rikai.com/library/kanjitables/kanji_codes.unicode.shtml)(warning: long page). All characters present in RSH and RTH books (Chinese) have also been added to the database (many simplified Chinese characters are not listed in KANJIDIC).

#### Request Format
All requests are made in REST format.
The REQUIRED parameter **api_key** is used to specify your API Key.

#### Response Format
All API responses are sent in JSON format (see [json.org](http://json.org/)).

A succesful response has `Content-Type: application/json; charset=utf-8` in the HTTP response header.

When a request is successful the response starts with the "stat" field with the value "ok":

    {
      "stat": "ok",
      //(misc. data follows...)
    }

When a request fails the following format is used. Note that this is a failure from the API logic, which is always HTTP 200 OK. A low level failure needs to be handled separately, such as HTTP 403, etc. and will not contain JSON.

    {
      "stat":    "fail",
      "code":    10,
      "message": "Error message"
    }


## API Methods

### Account

#### /account/info

Returns information about the current user.

URL STRUCTURE
`http://kanji.koohii.com/api/v1/account/info?api_key=TESTING` 

METHOD
`GET` 

SAMPLE JSON RESPONSE

    {
      "stat": "ok",
      "username": "hanky420",
      "location": "Tokyo Japan",
      "srs_info": {
        "flashcard_count": 500,
        "total_reviews": 124
      }
    }


### Keywords

#### /keywords/list

TODO


### Review

#### /review/fetch

Fetch flashcard information for up to N cards at a time (**limit_fetch** in /review/start response).

Use this to fetch flashcard displayed information as you advance through the items returned by [/review/start](#reviewstart) or your own custom set of flashcard ids (UCS codes). Ideally, you would call this once at the start of a review to get the first batch of cards, and then request more card data as the user advances through the set of cards.

This API method is NOT designed to return all card data at once, which is why it has a limit on the number of returned items. The reason for this is that in the future the flashcards may display "possible words" and any number of information that requires additional queries *for each* card, which is not efficient at all for the web app when requesting hundreds of cards at once.

A suggestion to handle reviews similarly to the Kanji Koohi javascripts works like this:

- store card data in a hash, using the card id as the key to manage a "cache" of card data
- fetch the initial set of card data at position #0 in your array of card ids (such as returned by `/review/start` or a custom sequence)
- fetch 10 more cards at position #5, #15, #25, etc. through the array of card ids. That way, the user will not wait for new cards to load since you will be fetching the next ten cards before the user finished reviewing the previously fetched cards.


yomi **(optional)**

  1 to include sample On/Kun readings, 0 to disable.

URL STRUCTURE
`http://kanji.koohii.com/api/v1/review/fetch?api_key=TESTING&yomi=1&items=20108,20845,19968,20843,19977,21313,22235,19971,20061,20116` 

METHOD
`GET` 

SAMPLE JSON RESPONSE
The response contains an array of objects. The kanji is *not* included.

    {
      "stat":       "ok",
      "card_data":      [
        {
          "keyword":      "correct",
          "id":           27491,
          "strokecount":  5,
          "framenum":     379
        }
      ]
    } 

If the **yomi** option is enabled, each **card_data** entry will also contain a sample on and kun words (when available, based on word frequency) that illustrate one On and Kun readings.

In this example the kanji 正 is enclosed in brackets along with the part of the reading that corresponds. You can use simple string substitition here to insert HTML elements for styling, or just remove them.

    {
      "card_data":      [
        {
          (...)
          "v_on": {
             "compound": "[正]午",
             "reading":  "[ショウ]ゴ",
             "gloss":    "noon; mid-day"
          },
          "v_kun":       false
        }
      ]
    } 

#### /review/start

Obtain a selection of flashcards for a review session. Mode is required and should be either **free** for unlimited reviews (no saved state), or **srs** to select cards from the user's flashcard set based on the card's status (new, due and failed cards).

The **free reviews** selection is really just a helper, since you can easily make your own selection of flashcard ids (kanji UCS code) by translating a range of Heisig indices, or any other criteria (eg. JLPT).

Note while testing you can repeat a SRS review any number of times so long as you don't update the flashcards. Keep in mind the selection returned for a SRS review can seem to change because it is shuffled by default (further, the shuffling of cards is done in sets of cards that expire on the same date, so that the most "urgent" cards still appear sooner in the selection).

mode

  free for free (infinite) reviews, srs for spaced repetitions

from (required)

  (FREE REVIEW) : Heisig index for start of range (based on user's RTK Edition setting)

to (required)

  (FREE REVIEW) : Heisig index for end of range (based on user's RTK Edition setting)

shuffle (optional)

  (FREE REVIEW) : 1 to shuffle the selection

type (required)

  (SRS REVIEW) : due (expired cards), new (untested, blue pile), failed (red pile), learned (red pile filtered by learned mark)

URL STRUCTURE
`http://kanji.koohii.com/api/v1/review/start?api_key=TESTING&mode=free&from=1&to=10&shuffle=1` 

`http://kanji.koohii.com/api/v1/review/start?api_key=TESTING&mode=srs&type=new` 

METHOD
`GET` 

SAMPLE JSON RESPONSE
**items** is an array of flashcard ids. Since Kanji Koohii only deals in kanji flashcards, the unicode value is used as a unique id. Approximately 20,000 kanji and hanzi that are supported on the website all have a UCS code that fits in 16 bit storage (ie. "smallint" in MySQL).

**limit_fetch** and **limit_sync** are the maximum number of items that can be handled by /review/fetch and /review/sync. These may change at a later time if the complexity of the flashcard data increases.

    {
      "stat":        "ok",
      "card_count":  10,
      "items":       [20108,20845,19968,20843,19977,21313,22235,19971,20061,20116],
      "limit_fetch": 10,
      "limit_sync":  50
    } 


#### /review/sync

Send flashcard answers back to the server. Use this **only for SRS reviews**, where the user has created flashcards. The server takes the answers and will update the card's "due" time, last review timestamp, etc. Note that the "DELETE" answer will actually delete the flashcard!

There is a built in limit of N cards (see **limit_sync** in /review/start response).

This can be used in two ways:

- To rate all the cards *at the end of a review session* (not recommended), you may call this multiple times (with a min. 1s pause in between requests).
- You can also sync flashcard answers *while a review is in progress*, as the user advances through the cards. For example if user is at position P in the array of flashcards, send all the answers up to P - 10 (leaving some room for a "Undo" functionality). Repeat every ten cards or so. This ensures the user can never lose too much progress if the app inadvertently closes.

Please do NOT sync one card at a time! It is easier on the server, and causes less delay in the app, to sync flashcard answers in batches.

URL STRUCTURE
`http://kanji.koohii.com/api/v1/review/sync` 

METHOD
`POST` : this method requires the data to be sent in JSON format with Content-Type: application/json HTTP header.

**time** is currently ignored, and should be set to 0.

**sync** is an array of objects, each object contains update information for a unique flashcard. **id** needs to be a unique identifier (here, the UCS code of the kanji). **r** is the SRS answer (see below).

    {
      "time": 54812541,
      "sync": [
        { "id": 20108, "r": 1 },
        { "id": 20845, "r": 5 },
        (...)
      ]
    } 

The flashcard ratings for the SRS are:

    NO      = 1
    YES     = 2
    EASY    = 3
    DELETE  = 4
    SKIP    = 5

SAMPLE JSON RESPONSE
**put**: is an array that confirms each item that has been succesfully updated (or deleted).

**ignored** (optional): if this is returned, it means the items have already been handled during this session and the card status has not been updated. A session is reset with /review/start . This is to avoid rating cards multiple times in case of an API request being sent twice.

    {
      "stat":    "ok",
      "put":     [22244,22242,22241,22240],
      "ignored": [22244]
    } 


### SRS

#### /srs/info

Returns SRS status information for the signed in user, as seen in the [review](http://kanji.koohii.com/review) page.

Note that unlike the Leitner chart seen on the website, this method does not filter between RTK1, RTK3, and non-RTK cards.

Information returned: total count of new (blue), due (orange) and failed/restudy (red) cards, plus the number of restudy cards marked as learned.

URL STRUCTURE
`http://kanji.koohii.com/api/v1/srs/info?api_key=TESTING` 

METHOD
`GET` 

SAMPLE JSON RESPONSE

    {
      "stat":           "ok",
      "new_cards":      20,
      "due_cards":      15,
      "relearn_cards":  5,
      "learned_cards":  2
    }


### Study

#### /study/sync

Send flashcard 'learned' marks back to the server. You can send a list of cards to be marked and a list of cards to be unmarked. This is only relevant for cards in the red pile, but there is no need to reference all of them.

There is a built in limit of N cards that applies to either list (see **limit_sync** in /review/start response).

Please try, as much as possible, to sync the marks in batches, instead of making an API call each time the user marks or unmarks a card.

URL STRUCTURE
`http://kanji.koohii.com/api/v1/study/sync` 

METHOD
`POST` : this method requires the data to be sent in JSON format with Content-Type: application/jsonHTTP header.

**learned**: is an array of ids (the UCS code of the kanji) for the flashcards that should be marked as learned.

**notLearned**: is an array of ids for the flashcards that should be **unmarked** as learned.
Note both arrays are required. Send an empty array when there are no ids to send for either list.

    {
      "learned": [22244,22242,22241],
      "notLearned": []
    } 

SAMPLE JSON RESPONSE

**putLearned**: is an array that confirms each item that has been succesfully marked as learned.

**putNotLearned**: is an array that confirms each item that has been succesfully unmarked as learned.

For successful operations, the arrays contain the same ids as the corresponding input. On failure, an empty array is returned.

    {
      "stat": "ok",
      "putLearned": [22244,22242,22241],
      "putNotLearned": []
    } 


#### /study/info

Returns the ids of the user’s restudy flashcards (red pile), plus the ids of flaschards currently marked as learned.

Note that the second list is a subset of the first one: any learned flashcards appear in both.

URL STRUCTURE
`http://kanji.koohii.com/api/v1/study/info?api_key=TESTING` 

METHOD
`GET` 

SAMPLE JSON RESPONSE

    {
      "stat": "ok",
      "items": [22244,22242,22241,22240],
      "learnedItems": [22242,22241],
    }

