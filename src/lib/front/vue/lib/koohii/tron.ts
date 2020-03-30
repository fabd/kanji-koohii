/**
 * A simple wrapper around JSON messages to standardize error handling in the ux
 *
 * Corresponds to backend lib/JsTron.php
 *
 *
 * Example:
 *
 *   import * as TRON from "@lib/koohii/tron"
 *
 *   interface UserVote { storyId: number, vote: boolean }
 *   let t = TRON.Inst<UserVote>(json)
 *   t.isSuccess() && console.log(t.getProps().storyId)
 *
 *
 * Methods:
 *
 *   TRON(json)         Factory function, sanitizes & always returns a valid TRON message
 *
 *   isSuccess()
 *   getStatus()
 *
 *   getProps()        @return {Object}
 *
 *   getErrors()       @return {Array}
 *   hasErrors()
 *   setErrors()       @param {...string}   One or more error messages
 *
 */

import Lang, { merge } from "./lang";

// keep in sync with constants in /lib/JsTRON.php

// TRON message was not found in given source
export enum STATUS {
  EMPTY = -1,
  // a form submission contains errors, or a blocker (do not close ajax dialog)
  FAILED = 0,
  // a form is submitted succesfully, proceed (eg. close ajax dialog)
  SUCCESS = 1,
  // a form submitted succesfully, and continues with another step
  PROGRESS = 2,
}

type Hash = { [key: string]: any };

export interface TronMessage {
  status: STATUS;
  props: Hash;
  errors: string[];
}

export interface TronInst<T = any> {
  isEmpty(): boolean;
  isSuccess(): boolean;
  isFailed(): boolean;
  getStatus(): STATUS;
  getProps(): T;
  getErrors(): string[];
  hasErrors(): boolean;
  setErrors(...errors: string[]): void;
}

export function Inst<T>(message: Partial<TronMessage>): TronInst<T> {
  console.assert(Lang.isObject(message), "TRON() : json is not an object");

  let tronObj: TronMessage = { status: STATUS.EMPTY, props: {}, errors: [] };
  merge(tronObj, message);

  const inst = {
    isEmpty: () => tronObj.status === STATUS.EMPTY,
    isSuccess: () => tronObj.status === STATUS.SUCCESS,
    isFailed: () => tronObj.status === STATUS.FAILED,

    getStatus: (): STATUS => {
      return tronObj.status;
    },
    getProps: (): T => tronObj.props as T,

    getErrors: () => tronObj.errors,
    hasErrors: () => tronObj.errors.length > 0,
    setErrors: function(...errors: string[]) {
      tronObj.errors.push(...errors);
    },
  };

  return inst;
}
