/**
 * TRON is a wrapper to abstract and standardize the JSON communications.
 * 
 * Features:
 * - Supports embedded HTML within JSON response.
 * - Abstract specifics of async requests from underlying framework (YUI).
 * - Status codes to return success/fail states.
 * - Error messages for server exceptions that can be shown to the user.
 *
 * Methods:
 *   isEmpty()      If true this means this instance is not created from a JSON response
 *                  that uses the TRON format.
 *   getStatus()
 *   getProps()
 *   getHtml()      Returns embedded html, or an empty string (note '' evaluates to false).
 *   getErrors()
 *   hasErrors()    Returns true if there are error messages.
 *
 *   isSuccess()    Proxy for (getStatus() === STATUS_SUCCESS)
 *
 * Global error handling with TRON.handleErrors():
 *
 *   Replace Core.Helper.TRON.handleErrors to customize the display of errors
 *   returned with the tron message.
 * 
 * @lint    jslint lib/front/corejs/core/core-json.js
 * @author  Fabrice Denis
 */
/*global YAHOO, Core */

if (!Core.Helper) {
  Core.Helper = {};
}

(function() {

  Core.Helper.TRON = Core.make();

  var Y = YAHOO,
      TRON = Core.Helper.TRON;

  // TRON message was not found in given source
  TRON.STATUS_EMPTY = -1;
  
  // a form submission contains errors, or a blocker (do not close ajax dialog)
  TRON.STATUS_FAILED = 0;
  // a form is submitted succesfully, proceed (eg. close ajax dialog)
  TRON.STATUS_SUCCESS = 1;
  // a form submitted succesfully, and continues with another step
  TRON.STATUS_PROGRESS = 2;

  /**
   * Helper for global handling of error messages. Replace with application
   * specific handler, for example displaying a proper dialog.
   */
  TRON.handleResponse = function(t)
  {
    var errors = t.getErrors();

    if (errors.length)
    {
      console.warn(errors.join('\n'));
    }
  };

  /**
   *
   */
  TRON.prototype =
  {
    oJson: null,

    /**
     * Constructor.
     *
     * @param  {Object|String}  json    A JSON string or native object.
     */
    init: function(json)
    {
      var oJson = {
        status:   TRON.STATUS_EMPTY,
        props:    {},
        html:     '',
        errors:   []
      };

      // in JSON format, not yet parsed
      if (Y.lang.isString(json))
      {
        // parse a JSON string
        try {
          oJson = JSON.parse(json);
        } catch (e) {
          console.error('Core.Helper.TRON()  Could not parse JSON (%o)', json);
        }
      }
      // should be a native object
      else
      {
        // use default properties for those not set in the TRON message
        oJson = { ...oJson, ...json };
      }

      // validate TRON message
      console.assert(Y.lang.isNumber(oJson.status), "TRON status is not a number");
      console.assert(Y.lang.isObject(oJson.props),  "TRON props is not an object");
      console.assert(Y.lang.isArray(oJson.errors),  "TRON errors is not an array");

      this.oJson = oJson;
    },

    /**
     * Checks whether the TRON message was set, or is using the default values.
     *
     * @return {boolean}   true if there was no TRON message.
     */
    isEmpty: function() {
      return this.oJson.status === TRON.STATUS_EMPTY;
    },

    /**
     * Checks if the TRON message returns a SUCCESS status.
     *
     * @return {boolean}   true if TRON status is success.
     */
    isSuccess: function() {
      return this.oJson.status === TRON.STATUS_SUCCESS;
    },

    /**
     * Returns the TRON message status, which can be compared against the
     * STATUS_xxxxx enum.
     *
     * @return {Number}    Returns TRON message status as a number.
     */
    getStatus: function() {
      return this.oJson.status;
    },

    getProps: function() {
      return this.oJson.props;
    },

    getHtml: function() {
      return this.oJson.html;
    },

    getErrors: function() {
      return this.oJson.errors;
    },

    hasErrors: function() {
      return this.oJson.errors.length > 0;
    }
  };

}());

