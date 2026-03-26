/**
 * HttpClient() is a wrapper around axios for making requests.
 *
 * - never fails & always returns a TRON message
 * - request errors are converted to TRON errors (tron.getErrors())
 */
import axios, { type AxiosInstance, type AxiosRequestConfig } from "axios";
import { Tron, STATUS } from "@lib/tron";

type ApiRequestConfig = Pick<AxiosRequestConfig, "method" | "params" | "data">;

const API_DEFAULT_TIMEOUT = 5000;

export abstract class HttpClient {
  protected readonly axiosInst: AxiosInstance;

  public constructor(baseURL: string) {
    this.axiosInst = axios.create({
      baseURL: baseURL,
      timeout: API_DEFAULT_TIMEOUT,
    });
  }

  // axios.get() proxy
  public get<T extends object>(uri: string, params: any) {
    const config: ApiRequestConfig = {
      method: "get",
      params,
    };
    return this.request<T>(uri, config);
  }

  // axios.post() proxy
  public post<T extends object>(uri: string, data: any) {
    const config: ApiRequestConfig = {
      method: "post",
      data,
    };
    return this.request<T>(uri, config);
  }

  // generic axios request() which handles the catch() and always resolves to a TRON message
  protected request<T extends object>(uri: string, config: ApiRequestConfig) {
    const requestConfig: AxiosRequestConfig = {
      method: config.method || "get",
      url: uri,
      params: config.params || null, // url parameters
      data: config.data || null, // request body, only PUT/POST/DELETE/PATCH
    };

    return this.axiosInst
      .request(requestConfig)
      .then((res) => {
        const t = Tron<T>(res.data);

        // helps debugging during development
        if (t.getStatus() === STATUS.FAILED || t.hasErrors()) {
          console.warn(
            "HttpClient() TRON error(s): \n" + t.getErrors().join("\n")
          );
        }

        return t;
      })
      .catch((error) => {
        // we basically never want to fail, and always return a valid tron message

        const t = Tron<T>({ status: STATUS.FAILED });

        // The request was made and the server responded with a status code that falls out of the range of 2xx
        if (error.response) {
          console.warn(
            "HttpClient(response error): status code ",
            error.response.status
          );
          t.setErrors(
            `Oops! Server responded with error ${error.response.status}`
          );
        }
        // The request was made but no response was received -- `error.request` is an instance of XMLHttpRequest in the browser
        else if (error.request) {
          console.warn("HttpClient(request error): ", error);
          t.setErrors(
            `Oops! The request timed out, please try again in a moment.`
          );
        }

        // Something happened in setting up the request that triggered an Error
        else {
          console.warn("HttpClient(unknown error): ", error.message);
          t.setErrors("Request error");
        }

        return Promise.resolve(t);
      });
  }
}
