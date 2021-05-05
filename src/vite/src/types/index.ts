import { DefineComponent, ComponentPublicInstance } from "vue";

export type TVueDefine = DefineComponent<{}, {}, any>;
export type TVuePropsData = Record<string, unknown>;
export type TVueInstance = ComponentPublicInstance;

export type Dictionary<T> = { [key: string]: T };
