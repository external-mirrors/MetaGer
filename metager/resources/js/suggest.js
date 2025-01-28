/**
 * MetaGers basic suggestion module
 */
export function initializeSuggestions() {
  let suggestions = [];
  let query = "";
  let suggest_timeout = null;
  let searchbar_container = document.querySelector(".searchbar");
  let on_startpage = document.querySelector("#searchForm .startpage-searchbar") != null;
  if (!searchbar_container) {
    return;
  }
  let suggestions_container = searchbar_container.querySelector(".suggestions");
  if (!suggestions_container) {
    return;
  }
  let suggestion_url = suggestions_container.dataset.suggestions;

  let search_input = searchbar_container.querySelector("input[name=eingabe]");
  if (!search_input) {
    return;
  }

  search_input.addEventListener("keydown", clearSuggestTimeout);
  search_input.addEventListener("keyup", (e) => {
    if (e.key == "Escape") {
      e.stopPropagation();
      e.target.blur();
    } else {
      clearSuggestTimeout();
      suggest_timeout = setTimeout(suggest, 600);
    }
  });
  search_input.addEventListener("paste", e => {
    e.preventDefault();
    search_input.value = e.clipboardData.getData("text");
    clearSuggestTimeout();
    suggest();
  });
  search_input.addEventListener("focusin", e => {
    e.preventDefault();
    suggest();
  });
  search_input.addEventListener("blur", e => {
    clearSuggestTimeout();
    setTimeout(() => {
      if (document.activeElement != search_input) searchbar_container.dataset.suggest = "inactive";
    }, 250);
  });

  search_input.form.addEventListener("submit", clearSuggestTimeout);

  function clearSuggestTimeout(e) {
    if (suggest_timeout != null) {
      clearTimeout(suggest_timeout);
      suggest_timeout = null;
    }
  }

  function suggest() {
    if (search_input.value.trim().length <= 3 || navigator.webdriver) {
      suggestions = [];
      updateSuggestions();
      return;
    }
    if (search_input.value.trim() == query) {
      updateSuggestions();
      return;
    } else {
      query = search_input.value.trim();
    }

    fetch(suggestion_url + "?query=" + encodeURIComponent(query), {
      method: "GET",
    })
      .then((response) => response.json())
      .then((response) => {
        suggestions = response;
        updateSuggestions();
      }).catch(reason => {
        suggestions = [];
        updateSuggestions();
      });
  }

  function updateSuggestions() {
    // Enable/Disable Suggestions
    if (suggestions.length > 0) {
      searchbar_container.dataset.suggest = "active";
    } else {
      searchbar_container.dataset.suggest = "inactive";
    }

    // Add all Suggestions
    let eingabe_container = document.querySelector("input[name=eingabe]");
    suggestions_container
      .querySelectorAll(".suggestion")
      .forEach((value, index) => {
        if (suggestions.length < index + 1) {
          value.style.display = "none";
          return;
        } else {
          value.style.display = "flex";
        }

        let search_button = value.querySelector("button");
        if (!search_button) return 1;
        let title_container = value.querySelector("span");
        if (!title_container) return 1;

        search_button.value = suggestions[index];
        title_container.textContent = suggestions[index];
        if (eingabe_container) {
          title_container.onclick = e => {
            e.preventDefault();
            console.log("test", suggestions[index]);
            eingabe_container.value = suggestions[index] + " ";
            eingabe_container.focus();
          };
        }
      });
    if (suggestions.length > 0 && on_startpage) {
      setTimeout(() => {
        let rect_bounds = searchbar_container.getBoundingClientRect();
        if (rect_bounds.top < 0 || rect_bounds.left < 0 || rect_bounds.bottom > (window.visualViewport.height || window.innerHeight || document.documentElement.clientHeight) || rect_bounds.right > (window.visualViewport.width || window.innerWidth || document.documentElement.clientWidth)) {
          searchbar_container.scrollIntoView(true);
        }
      }, 250);
    }
  }
}
