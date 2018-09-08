/**
 * Collect all the API calls in one place, decouples ux code from KoohiiRequest 
 *
 * import { KoohiiAPI, TRON } from '../lib/KoohiiAPI.js'
 * 
 */

import KoohiiRequest  from './KoohiiRequest.js'
import TRON           from './tron.js'

const KoohiiAPI = {

  postUserStory(ucsId, txtStory, handlers) {

    let data = {
      ucs_code: ucsId,
      txtStory: txtStory,
      chkPublic: false,
      reviewMode: false
    }

    KoohiiRequest.request('/study/editstory', { method: 'post', data: data }, handlers)
  }

}

export { KoohiiAPI, TRON }
