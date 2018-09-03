/**
 *
 *
 * 
 * Footprint:
 *
 *    axios       +12.6 kb   webpack minified (non gzipped)
 *    request     ???        too many dependencies
 *    superagent  +17.2 kb   ...
 */

// use axios for now
import axios from 'axios'

export default {

  /**
   * Put together a request to koohii server. Also abstracts the underlying axios
   * library if we want something else later (but use axios config as template for
   * the options).
   *
   * @param  String   uri
   * @param  Object   object containing 'params' and/or 'data'
   * 
   */
  request(uri, config, thenHandler) {

    const errorHandler = (error) => {
      Core.log('axios error: %o', error)
    }

    const defaultHandler = (res) => {
      // data; status, statusText, headers, config
      Core.log('api(axios) :: response %o', res)
    }

    let requestConfig = {
      method: config.method || 'get',
      url: uri,
      params: config.params || null,      // url parameters
      data:   config.data   || null       // PUT/POST/PATCH data
    }

    Core.log('api :: initiating request %o', requestConfig)

    axios(requestConfig)
      .then(thenHandler || defaultHandler)
      .catch(errorHandler)

  },

  postUserStory(ucsId, txtStory) {

    let data = {
      ucs_code: ucsId,
      txtStory: txtStory,
      chkPublic: false,
      reviewMode: false
    }

    this.request('/study/editstory', { method: 'post', data: data })

  }

}
