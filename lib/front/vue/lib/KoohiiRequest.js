/**
 * KoohiiRequest handles client/server ajax for the Vue build
 *
 * - decouple the ajax library from the ux code
 * - the UX only deals with "TRON" messages (our json wrapper)
 * - help to globally handle errors
 * - helps debugging
 *
 * 
 * TODO
 *
 *    - use a smaller library?
 * 
 *    axios       +12.6 kb   webpack minified (non gzipped)
 *    request     ???        too many dependencies
 *    superagent  +17.2 kb   ...
 */

// use axios for now
import axios from 'axios'

import TRON  from '../lib/koohii/tron.js'

const DEFAULT_TIMEOUT = 5000

export default {

  /**
   * Put together a request to koohii server.
   * 
   * - abstracts the underlying library (currently, axios)
   * - wraps JSON responses in a custom format
   *
   * @param {String} uri
   * @param {Object} config    'params' (url parameters) and/or 'data' (post body for POST/PUT/PATCH)
   * @param {Object} fn        Handlers that will receive TRON message
   *
   */
  request(uri, config, {then, error}) {

    const defaultError = (error) => {
      console.log('api :: request :: defaultError() %o', error)
    }

    const defaultThen = (res) => {
      // data; status, statusText, headers, config
      console.log('api :: request :: defaultThen() %o', res)
    }

    // default handlers to debug the json response as is
    then  = then  || defaultThen
    error = error || defaultError

    let requestConfig = {
      timeout: DEFAULT_TIMEOUT,                      // msecs
      method: config.method || 'get',
      url: uri,
      params: config.params || null,      // url parameters
      data:   config.data   || null       // PUT/POST/PATCH data
    }

    // console.log('api :: request() %o', requestConfig)

    axios(requestConfig)
      .then(function(res) {
        const t = TRON(res.data)

        // see the response in console (convenience)
        console.log("KoohiiRequest / props received: %o", t.getProps())

        // help debug
        if (t.hasErrors()) { console.warn("KoohiiRequest / errors: \n" + t.getErrors().join("\n")); }

        then(t)
      })
      .catch(function(error) {

        // we basically never want to fail, so the UX shows something and the user can try again

        let t = TRON({
          status: 0  // STATUS_FAILED
        })
        
        // The request was made and the server responded with a status code that falls out of the range of 2xx
        if (error.response) {
          t.setErrors(`Oops! Server responded with error ${error.response.status}`)
          then(t)
        }
        
        // The request was made but no response was received -- `error.request` is an instance of XMLHttpRequest in the browser
        else if (error.request) {
          console.warn('KoohiiRequest / request error (timeout?) %o', error)
          t.setErrors(`Oops! The request timed out, please try again in a moment.`)
          then(t)
        }

        // Something happened in setting up the request that triggered an Error
        else {
          console.warn('Error', error.message);
          t.setErrors('Request error')
          then(t)
        }
      })
  }
}
