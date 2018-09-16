/**
 * Collect all the API calls in one place, decouples ux code from KoohiiRequest 
 *
 * import { KoohiiAPI, TRON } from '../lib/KoohiiAPI.js'
 * 
 */

import KoohiiRequest  from './KoohiiRequest.js'
import TRON           from '../lib/koohii/tron.js'

const KoohiiAPI = {

  /**
   *
   * RESPONSE
   *
   *   formattedStory   string
   *
   *   isFavoriteStory  boolean
   *   isStoryShared    boolean     ... story is currently shared
   *
   *   sharedStoryId    string      ... unique id for the "shared story" added to the page
   *   profileLink      string      ... author link in the "shared story" template
   *
   *
   * @param {object} storyInfo   { number, string, boolean, boolean }
   * @param {object} handlers    then & error handlers (cf. KoohiiRequest)
   */
  postUserStory({ucsId, txtStory, isPublic, reviewMode}, handlers)
  {
    let data = {
      ucs_code:   ucsId,
      txtStory:   txtStory,
      chkPublic:  isPublic,
      reviewMode: reviewMode
    }

    KoohiiRequest.request('/study/editstory', { method: 'post', data: data }, handlers)
  },

  // refactoring...
  ajaxSharedStory(data, handlers)
  {
    KoohiiRequest.request('/study/ajax', { method: 'post', data: data }, handlers)
  }
}

export { KoohiiAPI, TRON }
