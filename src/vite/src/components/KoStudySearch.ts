// The Study page search box autocomplete

interface IAutoCompleteOptions {
  inputElement: HTMLInputElement;
  dropdownElement: HTMLElement;
  data: string[];
  maxResults?: number;
  onSelect: (word: string) => void;
}

export default class AutoComplete {
  private input: HTMLInputElement;
  private dropdown: HTMLElement;
  private data: string[];
  private maxResults: number;
  private activeIndex: number;
  private filteredItems: string[];
  private onSelect: (word: string) => void;

  constructor(options: IAutoCompleteOptions) {
    this.input = options.inputElement;
    this.dropdown = options.dropdownElement;
    this.data = options.data;
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

    // FIXME : this could be optimized to exit early when maxResults matches found
    //  instead of using slice()
    this.filteredItems = this.data
      .filter((word) => word.toLowerCase().includes(query))
      .slice(0, this.maxResults);

    if (this.filteredItems.length === 0) {
      this.hideDropdown();
      return;
    }

    this.render(query);
  }

  private render(query: string) {
    this.dropdown.innerHTML = "";

    this.filteredItems.forEach((word, index) => {
      const li = document.createElement("li");
      li.className =
        "ko-StudySearchDD-item px-4 py-2 border-b last:border-b-0 border-gray-100 text-gray-700 transition-colors";

      if (index === this.activeIndex) {
        li.classList.add("active");
      }

      // Highlight matching text
      const startIndex = word.toLowerCase().indexOf(query);
      const before = word.substring(0, startIndex);
      const middle = word.substring(startIndex, startIndex + query.length);
      const after = word.substring(startIndex + query.length);

      li.innerHTML = `${before}<em>${middle}</em>${after}`;

      li.dataset.word = word; // for the dropdown click event

      this.dropdown.appendChild(li);
    });

    this.dropdown.classList.remove("hidden");
  }

  private onClickItem(e: Event) {
    e.preventDefault();
    const word = (e.target as HTMLElement).dataset.word!;
    this.selectWord(word);
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
          this.selectWord(this.filteredItems[this.activeIndex]!);
        }
        break;
      case "Enter": {
        if (isVisible) {
          this.selectWord(this.filteredItems[this.activeIndex]!);
          break;
        }
        const searchText = this.input.value.trim();
        if (searchText) this.onSelect(this.input.value.trim());
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
      item.classList.toggle("active", index === this.activeIndex);
    });
  }

  private selectWord(word: string) {
    this.input.value = word;
    this.hideDropdown();
    this.onSelect(word);
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
