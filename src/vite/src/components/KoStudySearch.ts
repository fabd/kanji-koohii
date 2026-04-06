// The Study page search box autocomplete

import { getKeywordForUCS } from "@/lib/rtk";

export interface IAutoCompleteItem {
  keyword: string;
  kanji: string;
  index: number;
}

interface IAutoCompleteOptions {
  inputElement: HTMLInputElement;
  dropdownElement: HTMLElement;
  /** kanji string where kanjis[i] is the kanji for frame index i+1 */
  kanjis: string;
  maxResults?: number;
  onSelect: (value: string) => void;
}

export default class AutoComplete {
  private input: HTMLInputElement;
  private dropdown: HTMLElement;
  private kanjis: string;
  private maxResults: number;
  private activeIndex: number;
  private filteredItems: IAutoCompleteItem[];
  private onSelect: (value: string) => void;

  constructor(options: IAutoCompleteOptions) {
    this.input = options.inputElement;
    this.dropdown = options.dropdownElement;
    this.kanjis = options.kanjis;
    this.maxResults = options.maxResults ?? 10;

    this.activeIndex = 0;
    this.filteredItems = [];
    this.onSelect = options.onSelect;

    this.init();
  }

  private init() {
    this.input.addEventListener("input", this.onInput.bind(this));
    this.input.addEventListener("keydown", this.onKeyDown.bind(this));
    document.addEventListener("click", this.onClickOutside.bind(this));
    this.dropdown.addEventListener("click", this.onClickItem.bind(this));
  }

  private onInput() {
    const query = this.input.value.toLowerCase();
    this.activeIndex = 0;

    if (!query) {
      this.hideDropdown();
      return;
    }

    // Digit-only query: exact lookup by frame index
    if (/^\d+$/.test(query)) {
      const i = parseInt(query, 10) - 1;
      if (i >= 0 && i < this.kanjis.length) {
        const kanji = this.kanjis[i]!;
        this.filteredItems = [{
          keyword: getKeywordForUCS(kanji.codePointAt(0)!),
          kanji,
          index: i + 1,
        }];
        this.render(query);
      } else {
        this.hideDropdown();
      }
      return;
    }

    const matched: IAutoCompleteItem[] = [];
    for (let i = 0; i < this.kanjis.length; i++) {
      const kanji = this.kanjis[i]!;
      const keyword = getKeywordForUCS(kanji.codePointAt(0)!);
      if (keyword.toLowerCase().includes(query)) {
        matched.push({
          keyword,
          kanji,
          index: i + 1,
        });
        if (matched.length === this.maxResults) break;
      }
    }
    this.filteredItems = matched;

    if (this.filteredItems.length === 0) {
      this.hideDropdown();
      return;
    }

    this.render(query);
  }

  private render(query: string) {
    this.dropdown.innerHTML = "";

    this.filteredItems.forEach((item, index) => {
      const li = document.createElement("li");
      li.className = "ko-StudySearchDD-item";

      if (index === this.activeIndex) {
        li.classList.add("is-active");
      }

      // Highlight matching part of keyword (startIndex is -1 for digit queries)
      const startIndex = item.keyword.toLowerCase().indexOf(query);
      let keywordHtml: string;
      if (startIndex === -1) {
        keywordHtml = item.keyword;
      } else {
        const before = item.keyword.substring(0, startIndex);
        const middle = item.keyword.substring(startIndex, startIndex + query.length);
        const after = item.keyword.substring(startIndex + query.length);
        keywordHtml = `${before}<em>${middle}</em>${after}`;
      }

      li.innerHTML =
        `<span class="ko-StudySearchDD-keyword">${keywordHtml}</span>` +
        `<span class="ko-StudySearchDD-kanji cj-k" lang="ja">${item.kanji}</span>` +
        `<span class="ko-StudySearchDD-index">${item.index}</span>`;

      li.dataset.kanji = item.kanji;

      this.dropdown.appendChild(li);
    });

    this.dropdown.classList.remove("hidden");
  }

  private onClickItem(e: Event) {
    e.preventDefault();
    const li = (e.target as HTMLElement).closest<HTMLElement>("li");
    if (li?.dataset.kanji) this.selectItem(li.dataset.kanji);
  }

  private onKeyDown(e: KeyboardEvent) {
    const isVisible = !this.dropdown.classList.contains("hidden");

    switch (e.key) {
      case "ArrowDown":
        if (!isVisible) break;
        e.preventDefault();
        this.activeIndex = (this.activeIndex + 1) % this.filteredItems.length;
        this.updateVisualSelection();
        break;
      case "ArrowUp":
        if (!isVisible) break;
        e.preventDefault();
        this.activeIndex =
          (this.activeIndex - 1 + this.filteredItems.length) %
          this.filteredItems.length;
        this.updateVisualSelection();
        break;
      case "Tab":
        if (isVisible) {
          e.preventDefault();
          this.selectItem(this.filteredItems[this.activeIndex]!.kanji);
        }
        break;
      case "Enter": {
        if (isVisible) {
          this.selectItem(this.filteredItems[this.activeIndex]!.kanji);
          break;
        }
        const searchText = this.input.value.trim();
        if (searchText) this.onSelect(searchText);
        break;
      }
      case "Escape":
        if (isVisible) this.hideDropdown();
        break;
    }
  }

  private updateVisualSelection() {
    const items = this.dropdown.querySelectorAll<HTMLLIElement>("li");
    items.forEach((item, index) => {
      item.classList.toggle("is-active", index === this.activeIndex);
    });
  }

  private selectItem(kanji: string) {
    this.input.value = kanji;
    this.hideDropdown();
    this.onSelect(kanji);
  }

  private hideDropdown() {
    this.dropdown.classList.add("hidden");
    this.activeIndex = 0;
  }

  private onClickOutside(e: MouseEvent) {
    if (
      !this.input.contains(e.target as Node) &&
      !this.dropdown.contains(e.target as Node)
    ) {
      this.hideDropdown();
    }
  }
}
