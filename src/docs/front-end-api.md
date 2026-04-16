# Front-End API

## Overview

The front-end API stack consists of three layers:

- **`HttpClient`** (`vite/src/app/api/http-client.ts`) — abstract Axios wrapper. Never throws; always resolves to a Tron response.
- **`LegacyApi`** (`vite/src/app/api/api.ts`) — extends `HttpClient`, defines all API endpoints.
- **`models.ts`** (`vite/src/app/api/models.ts`) — TypeScript types for request/response payloads.

## The Tron Response Envelope

Every API call returns a **Tron** instance — a standardized envelope that wraps the server response. HTTP errors, timeouts, and server exceptions are all converted to Tron errors so callers never need to handle rejected promises.

```ts
// vite/src/lib/tron.ts

export type TronMsg<T extends object> = {
  status: STATUS;   // EMPTY | FAILED | SUCCESS
  props: T;         // typed response payload
  html: string;     // optional embedded HTML
  errors: string[]; // user-facing error messages
};
```

### `TronInst<T>` methods

| Method        | Description                                         |
| ------------- | --------------------------------------------------- |
| `isSuccess()` | `true` when status is `SUCCESS`                     |
| `isFailed()`  | `true` when status is `FAILED`                      |
| `isEmpty()`   | `true` when the response was not a TRON message     |
| `getStatus()` | Returns `STATUS` enum value                         |
| `getProps()`  | Returns the typed `props` payload                   |
| `getHtml()`   | Returns embedded HTML string (empty string if none) |
| `hasErrors()` | `true` when there are one or more errors            |
| `getErrors()` | Returns `string[]` of error messages                |

## Using the API

Import `getApi()` to get the singleton `LegacyApi` instance:

```ts
import { getApi } from "@/app/api/api";
import type { PostUserStoryResponse } from "@/app/api/models";
import { type TronInst } from "@/lib/tron";
```

Call an endpoint and handle the Tron response:

```ts
getApi()
  .postUserStory(
    this.kanjiData.ucs_id,
    this.postStoryEdit,
    this.postStoryPublic,
    this.isReviewMode
  )
  .then((tron: TronInst<PostUserStoryResponse>) => {
    if (!tron.hasErrors()) {
      const props = tron.getProps();
      // use props.initStoryView, props.isStoryShared, etc.
    } else {
      const errors = tron.getErrors(); // string[]
    }
  });
```

The `.then()` callback always fires — the promise never rejects.

## Adding a New Endpoint

### 1. Declare the response type in `models.ts`

```ts
// vite/src/app/api/models.ts

export type PostMyActionResponse = {
  ucsId: number;
  result: string;
};
```

### 2. Add the method to `LegacyApi` in `api.ts`

```ts
// vite/src/app/api/api.ts

postMyAction(ucsId: number, value: string) {
  // reaches module `study`, action `myaction` in the Symfony app (studyActions::executeMyaction)
  return this.post<PostMyActionResponse>("/study/myaction", {
    ucsId,
    value,
  });
}
```

Use `this.post()` for POST requests and `this.get()` for GET requests. The first argument is the URL path; the second is the request body (`post`) or query parameters (`get`).

### 3. Call it from a component

```ts
import { getApi } from "@/app/api/api";
import type { PostMyActionResponse } from "@/app/api/models";
import { type TronInst } from "@/lib/tron";

getApi()
  .postMyAction(ucsId, "some value")
  .then((tron: TronInst<PostMyActionResponse>) => {
    if (!tron.hasErrors()) {
      const props = tron.getProps();
      console.log(props.result);
    }
  });
```

## Backend Counterpart

On the PHP side, a Tron response is produced using `JsTron` (see `lib/JsTron.php`).

The URL pattern `/study/myaction` maps to module `study`, action `myaction` in the Symfony app:
`apps/koohii/modules/study/actions/actions.class.php` → `executeMyaction()`.

A typical Symfony action returning a Tron response:

```php
public function executeMyaction(sfWebRequest $request)
{
  // note the front-end API sends GET/POST requests in JSON
  $params = $request->getContentJson();

  $ucsId = (int) $params->ucsId;
  $value = $params->value;

  $tron = new JsTron();

  // ... if there is an error
  $tron->addError('Update failed.');
  $tron->setStatus(JsTron::STATUS_FAILED);

  // ... add a single prop to the response
  $tron->set('items', $this->getDictListItems($ucsId));

  // ... add multiple props to the response
  $tron->add([
    'ucsId'  => $ucsId,
    'result' => 'ok',
  ]);

  // sets the response content to JSON
  return $this->renderJson($tron);
}
```
