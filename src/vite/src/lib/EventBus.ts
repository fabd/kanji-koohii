/**
 * EventBus is a globally shared instance of EventDispatcher.
 *
 * Import and use directly without creating an instance:
 *
 *   import eventBus from "@lib/EventBus";
 *
 *   eventBus.connect("myEvent", handler);
 *   eventBus.notify("myEvent", payload);
 */

import EventDispatcher from "@lib/EventDispatcher";

const eventBus = new EventDispatcher();

export default eventBus;
