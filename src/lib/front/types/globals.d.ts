// globals for legacy JS files using implicit TypeScript support in VSCode

interface Window {
  Vue: any;
}

// global namespaces exposed by legacy JS
declare var App: any;
declare var Core: any;

// global exposed by YUI library (used by legacy JS)
declare var YAHOO: any;