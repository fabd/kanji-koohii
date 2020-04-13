/**
 * KoohiiRequest handles client/server ajax for the Vue build
 *
 * - decouple the ajax library from the ux code
 * - use a wrapper around JSON responses (encapsulation)
 * - help to globally handle errors
 * - helps debugging
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
import axios, { AxiosRequestConfig, Method } from "axios";

import * as TRON from "@lib/koohii/tron";

const DEFAULT_TIMEOUT = 5000;

interface KoohiiRequestConfig {
  method?: Method;
  params?: any;
  data?: any;
}

export interface KoohiiRequestHandlers<T> {
  then: (t: TRON.TronInst<T>) => void;
  error?: (t: TRON.TronInst<T>) => void;
}

/**
 * Put together a request to koohii server.
 *
 * - abstracts the underlying library (currently, axios)
 * - wraps JSON responses in a custom format
 *
 */
export default {
  request<T>(
    uri: string,
    config: KoohiiRequestConfig,
    handlers: KoohiiRequestHandlers<T>
  ) {
    const defaultError = (error: string) => {
      console.log("api :: request :: defaultError() %o", error);
    };

    const defaultThen = (res: any) => {
      // data; status, statusText, headers, config
      console.log("api :: request :: defaultThen() %o", res);
    };

    // default handlers to debug the json response as is
    let then = handlers.then || defaultThen;
    // let error = handlers.error || defaultError;

    let requestConfig: AxiosRequestConfig = {
      timeout: DEFAULT_TIMEOUT, // msecs
      method: config.method || "get",
      url: uri,
      params: config.params || null, // url parameters
      data: config.data || null, // PUT/POST/PATCH data
    };

    // console.log('api :: request() %o', requestConfig)

    axios(requestConfig)
      .then((res) => {
        const t = TRON.Inst<T>(res.data);

        // see the response in console (convenience)
        // console.log("KoohiiRequest / props received: %o", t.getProps())

        // help debug
        if (t.hasErrors()) {
          console.warn("KoohiiRequest / errors: \n" + t.getErrors().join("\n"));
        }

        then(t);
      })
      .catch((error) => {
        // we basically never want to fail, so the UX shows something and the user can try again

        let t = TRON.Inst<any>({ status: 0 /* STATUS_FAILED */ });

        // The request was made and the server responded with a status code that falls out of the range of 2xx
        if (error.response) {
          t.setErrors(
            `Oops! Server responded with error ${error.response.status}`
          );
          then(t);
        }

        // The request was made but no response was received -- `error.request` is an instance of XMLHttpRequest in the browser
        else if (error.request) {
          console.warn("KoohiiRequest / request error (timeout?) %o", error);
          t.setErrors(
            `Oops! The request timed out, please try again in a moment.`
          );
          then(t);
        }

        // Something happened in setting up the request that triggered an Error
        else {
          console.warn("Error", error.message);
          t.setErrors("Request error");
          then(t);
        }
      });
  },
};
