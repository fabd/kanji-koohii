/**
 * AjaxRequest is a wrapper for Axios.
 *
 * AjaxRequestOptions
 *
 *   method     Defaults to GET.
 *   params     This becomes `data` in Axios if using POST.
 *              To pass a query string, use `new URLSearchParams("foo=1&bar=2")`.
 *   form       If this is set, the form data will be added to the params.
 *   useJSON    Set true to use JSON for POST request.
 *
 */
import axios, { type AxiosRequestConfig } from "axios";

type HttpMethod = AxiosRequestConfig["method"];

/**
 * params becomes AxiosRequestConfig "data" in a POST request
 */
export type AjaxRequestOptions = {
  method?: HttpMethod;
  params?: URLSearchParams | Record<string, any>;
  form?: HTMLFormElement;
  timeout?: number;
  useJSON?: boolean;
};

function serializeForm(form: HTMLFormElement): Record<string, string> {
  const data: Record<string, string> = {};
  const formData = new FormData(form);
  formData.forEach((value, key) => {
    data[key] = value.toString();
  });
  return data;
}

const ajaxRequest = (url: string, options: AjaxRequestOptions = {}) => {
  const { form, timeout = 5000, method = "GET", useJSON = false } = options;
  let params = options.params || {};

  if (params instanceof URLSearchParams) {
    const record: Record<string, string> = {};
    params.forEach((value, key) => {
      record[key] = value;
    });
    params = record;
  }

  params = { ...params, ...(form ? serializeForm(form) : {}) };

  const config: AxiosRequestConfig = {
    method,
    url,
    timeout,
  };

  if (method.toUpperCase() === "GET") {
    config.params = params;
  } else if (useJSON) {
    // Axios uses "application/json" by default if params is an object
    config.data = params;
  } else {
    // Axios uses "application/x-www-form-urlencoded" if we pass a URLSearchParams()
    config.data = new URLSearchParams(params);
  }

  return axios(config);
};

export default ajaxRequest;
