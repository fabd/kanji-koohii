// FIXME: legacy componet, should become a Vue at some point

import $$, { domGetById, insertBefore } from "@lib/dom";
import { getApi } from "@app/api/api";
import AjaxTable from "@old/ajaxtable";
import EventDelegator from "@lib/EventDelegator";

// story in collapsed view with small "Unhide" link
const CLASS_HIDDEN_STORY = "is-moderated";

class SharedStoriesComponent {
  /** @type {EventDelegator} */
  evtDel = null;

  /**
   *
   * @param  {Element}  elContainer   Main div
   */
  constructor(elContainer) {
    // handling of votes, etc
    this.evtDel = (new EventDelegator(elContainer))
      .on("click", ".JsNewest", this.onNewestClick, this)
      .on("click", ".JsCopy", this.onCopy, this)
      .on("click", ".JsReport", this.onReport, this)
      .on("click", ".JsStar", this.onStar, this);

    // link that unhide most reported stories
    this.evtDel.on("click", ".JsUnhide", this.onUnhide, this);

    // handling of the stories paging
    new AjaxTable("SharedStoriesListComponent", {
      errorDiv: "SharedStoriesError",
    });
  }

  /**
   * 
   * @param {Event} ev 
   * @param {HTMLElement} el 
   */
  onUnhide(ev, el) {
    console.log("SharedStoriesComponent::onUnhide");
    if (ev.type === "click") {
      let storyDiv = el.closest(".sharedstory");      
      if (storyDiv) {
        storyDiv.classList.remove(CLASS_HIDDEN_STORY);
        $$(el).display(false);
      }
    }

    return false;
  }

  onNewestClick(ev, el) {
    if (ev.type === "click") {
      var div = domGetById("sharedstories-new");

      this.hideStories = !this.hideStories;
      div.classList.toggle("JsHide", this.hideStories);

      return false;
    } else {
      var ofs = ev.type === "mouseover" ? -33 : 0;
      $$(el).css("backgroundPosition", "0 " + ofs + "px");
    }
  }

  onCopy(ev, el) {
    ev.preventDefault();
    this.onClickStory("copy", el);
  }
  onReport(ev, el) {
    ev.preventDefault();
    this.onClickStory("report", el);
  }
  onStar(ev, el) {
    ev.preventDefault();
    this.onClickStory("star", el);
  }

  // returns "star" "report" or "copy" from the element class name
  getFirstClassName(el) {
    if (/(\w+)/.test(el.className)) {
      return RegExp.$1;
    }
    return "";
  }

  // refactor this with throttle or debounce() ..
  throttleClick() {
    var nowclick, nowsecs;

    nowclick = new Date().getTime();
    nowsecs = this.lastclick ? nowclick - this.lastclick : 1000;

    this.lastclick = nowclick;

    if (nowsecs < 300) {
      // span.className = 'err';
      // span.innerHTML = 'Not too fast please!';
      // console.log('throttleClick()');
      return true;
    }

    return false;
  }

  /**
   * @param {"copy"|"report"|"star"} which     action from the element's class name
   * @param {Element} el
   */
  onClickStory(which, el) {
    if (this.throttleClick()) {
      return;
    }

    // eg. "story-14266-22679"
    let elActions = el.closest(".JsAction");
    let storyIds = elActions.dataset;

    // userid, ucs_id
    var params = {
      // use the class name (star/report/copy) as "request"
      request: which,
      uid: storyIds.uid,
      sid: storyIds.cid,
    };

    if (params.request) {
      const elSharedStory = this.getStoryParentDiv(elActions);

      getApi()
        .legacy.ajaxSharedStory(params)
        .then((tron) => {
          this.onAjaxResponse(tron, elSharedStory);
        });
    }

    return;
  }

  // refactoring! is now a KoohiiRequest handler!
  onAjaxResponse(tron, elClickedStory) {
    var data = tron.getProps();

    // console.log('onAjaxResponse tron %o    el %o', tron, elClickedStory);

    if (!data) {
      return;
    }

    if (data.__debug_log) {
      var dbg_div = document.getElementById("__debug_log");
      if (dbg_div) {
        dbg_div.innerHTML = data.__debug_log;
      }
      // console.log(data.__debug_log);
    }

    // copy & edit story
    if (data.storyText) {
      Koohii.Refs.vueEditStory.onCopySharedStory(data.storyText);

      return;
    }

    var storyId = `story-${data.uid}-${data.sid}`;
    var actionsEl = $$("#" + storyId)[0];
    var msgEl = $$(".JsMsg", actionsEl)[0];
    var s;

    if (data.vote >= 0) {
      // sigh... NEED VUEJS  maintaining this code ... >_>
      var anchors = [];

      anchors[0] = $$(".JsStar span", actionsEl)[0];
      anchors[1] = $$(".JsReport span", actionsEl)[0];

      if (!data.vote && data.lastvote) {
        s = "Vote cancelled";
      } else if (data.vote === 1) {
        s = "Starred!";
      } else if (data.vote === 2) {
        s = "Reported";
      }

      // update counts
      var stars = actionsEl.getAttribute("appv1") || "0";
      var kicks = actionsEl.getAttribute("appv2") || "0";
      stars = parseInt(stars, 10) + parseInt(data.stars, 10);
      kicks = parseInt(kicks, 10) + parseInt(data.kicks, 10);
      actionsEl.setAttribute("appv1", stars);
      actionsEl.setAttribute("appv2", kicks);
      anchors[0].innerHTML = stars ? stars + "&nbsp;" : "&nbsp;";
      anchors[1].innerHTML = kicks ? kicks + "&nbsp;" : "&nbsp;";

      // move story to favourite(s)
      if (data.vote === 1) {
        this.moveStoryToFavourites(this.getStoryParentDiv(actionsEl), storyId);
      } else if (data.vote === 0 && data.lastvote === 1) {
        this.moveStoryBack(elClickedStory, storyId);
      }

      msgEl.innerHTML = "";
    } else {
      s = "No self vote!";
      msgEl.innerHTML = s;
    }
  }

  // helper that returns the main div (parent element) of a Shared Story
  getStoryParentDiv(el) {
    return el.closest(".sharedstory");
  }

  moveStoryToFavourites(elSharedStory, storyId) {
    var elFavourites = domGetById("sharedstories-top");

    if (!this.movedStory) {
      this.movedStory = {};
    }

    if (!this.movedStory[storyId]) {
      // insert a new empty div as a kind of bookmark to where the story was
      var div = document.createElement("div");
      div.id = this.moveStoryId(storyId);

      // insert our "remember this story position" div before the Shared Story div
      elSharedStory.insertAdjacentElement("beforeBegin", div);

      this.movedStory[storyId] = true;
    }

    // then move the Shared Story div to the favourites section
    $$(elSharedStory).css({ opacity: 0.1 });
    var anim = new YAHOO.util.Anim(
      elSharedStory,
      { opacity: { /*from:0.1,*/ to: 1.0 } },
      /* duration */ 1
    );

    elFavourites.appendChild(elSharedStory);

    anim.animate();
  }

  moveStoryId(storyId) {
    return storyId.replace("story-", "moved-");
  }

  moveStoryBack(elSharedStory, storyId) {
    var elMoveTo = $$("#" + this.moveStoryId(storyId))[0];

    if (elMoveTo) {
      console.log("moveStoryBack(): move story back to %o", elMoveTo);

      // if no page refresh or stories paging happened, the div we created can be used
      // to move the story back in the list where it was
      insertBefore(elSharedStory, elMoveTo);
    } else {
      // otherwise, avoid unnecessary complexity, just remove the div from the Favourites section
      elSharedStory.parentNode.removeChild(elSharedStory);
    }
  }
}

export default SharedStoriesComponent;
