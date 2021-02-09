/**
 * TRON is a simple wrapper around JSON messages.
 *
 * The main idea is that the UX never needs to deal with HTTP response codes,
 * or any server failure. From the point of view of the UX (ie. Vue components)
 * the operation is either succesful or not, and the response is *always* in
 * a standard format, even if the request or connection failed.
 *
 * Corresponds to backend lib/JsTron.php
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

import Lang from "@core/lang";

// keep in sync with constants in /lib/JsTRON.php

// TRON message was not found in given source
export const enum STATUS {
  EMPTY = -1,
  // a form submission contains errors, or a blocker (do not close ajax dialog)
  FAILED = 0,
  // a form is submitted succesfully, proceed (eg. close ajax dialog)
  SUCCESS = 1,
  // a form submitted succesfully, and continues with another step
  PROGRESS = 2,
}

type Hash = { [key: string]: any };

export interface TronMessage<T = Hash> {
  status: STATUS;
  props: T;
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

export function Inst<T = any>(message: Partial<TronMessage>): TronInst<T> {
  console.assert(Lang.isObject(message), "TRON() : json is not an object");

  const emptyTronMsg: TronMessage = {
    status: STATUS.EMPTY,
    props: {},
    errors: [],
  };
  const tronObj = { ...emptyTronMsg, ...message };

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
