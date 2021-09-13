type Dictionary<T = any> = { [key: string]: T };

// --------------------------------------------------------------------
// legacy component definitions; for .js files imported in .ts
// --------------------------------------------------------------------

interface ISelectionTable {
  new (container: string | Element): this;
  destroy(): void;
  getPostData(): Dictionary;
}
