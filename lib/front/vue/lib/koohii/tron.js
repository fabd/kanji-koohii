/**
 * A simple wrapper around JSON messages to standardize error handling in the ux
 *
 * Corresponds to backend lib/JsTron.php
 *
 *
 * Example:
 *
 *   let data = TRON(json).getProps()
 *
 *
 * Methods:
 * 
 *   TRON(json)         Factory function, sanitizes & always returns a valid TRON message
 * 
 *   isSuccess()
 *   getStatus()
 *
 *   getProps()
 *
 *   getErrors()
 *   hasErrors()
 *   
 */

// keep in sync with constants in /lib/JsTRON.php

// TRON message was not found in given source
const STATUS_EMPTY = -1
// a form submission contains errors, or a blocker (do not close ajax dialog)
const STATUS_FAILED = 0
// a form is submitted succesfully, proceed (eg. close ajax dialog)
const STATUS_SUCCESS = 1
// a form submitted succesfully, and continues with another step
const STATUS_PROGRESS = 2


export default function(json = {})
{
  if (typeof(json) !== 'object') {
    console.warn('TRON() json is not an object')
  }

  // merge
  let oJson    = json
  oJson.status = json.status || STATUS_EMPTY
  oJson.props  = json.props  || {}
  oJson.errors = json.errors || []

  return {
    isEmpty:     () => (oJson.status === STATUS_EMPTY),
    isSuccess:   () => (oJson.status === STATUS_SUCCESS),
    isFailed:    () => (oJson.status === STATUS_FAILED),

    getStatus:   () => oJson.status,
    getProps:    () => oJson.props,

    getErrors:   () => oJson.errors,
    hasErrors:   () => (oJson.errors.length > 0)
  }
}
