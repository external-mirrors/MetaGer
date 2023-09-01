/**
 * MetaGers basic suggestion module
 */
let suggestions = [];
let partners = [];
let query = "";

let suggest_timeout = null;
(() => {
  let searchbar_container = document.querySelector(".searchbar");
  if (!searchbar_container) {
    return;
  }
  let suggestions_container = searchbar_container.querySelector(".suggestions");
  if (!suggestions_container) {
    return;
  } else {
    suggestions_container.style.display = "grid";
  }
  let suggestion_url_partner = suggestions_container.dataset.partners;
  let suggestion_url = suggestions_container.dataset.suggestions;
  let key = suggestions_container.dataset.suggest;
  if (!key || typeof key != "string" || key.length == 0) {
    return;
  }
  let search_input = searchbar_container.querySelector("input[name=eingabe]");
  if (!search_input) {
    return;
  }

  search_input.addEventListener("keydown", clearSuggestTimeout);
  search_input.addEventListener("keyup", (e) => {
    if (e.key == "Escape") {
      e.target.blur();
    } else {
      clearSuggestTimeout();
      suggest_timeout = setTimeout(suggest, 800);
    }
  });
  search_input.addEventListener("focusin", suggest);
  search_input.addEventListener("change", (e) => {
    if (search_input.value.trim() == "") {
      query = "";
      searchbar_container.dataset.suggest = "inactive";
    }
  });
  search_input.form.addEventListener("submit", clearSuggestTimeout);

  function clearSuggestTimeout(e) {
    if (suggest_timeout != null) {
      clearTimeout(suggest_timeout);
    }
  }

  function suggest() {
    if (search_input.value.trim().length <= 3 || navigator.webdriver) {
      suggestions = [];
      partners = [];
      updateSuggestions();
      return;
    }
    if (search_input.value.trim() == query) {
      return;
    } else {
      query = search_input.value.trim();
    }

    fetch(suggestion_url_partner + "?query=" + encodeURIComponent(query), {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        "MetaGer-Key": key,
      },
    })
      .then((response) => response.json())
      .then((response) => {
        partners = response;
        updateSuggestions();
      }).catch(reason => {
        partners = [];
        updateSuggestions();
      });

    fetch(suggestion_url + "?query=" + encodeURIComponent(query), {
      method: "GET",
      headers: {
        "MetaGer-Key": key,
      },
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
    if (suggestions.length > 0 || partners.length > 0) {
      searchbar_container.dataset.suggest = "active";
    } else {
      searchbar_container.dataset.suggest = "inactive";
    }

    // Add all Partners
    suggestions_container
      .querySelectorAll(".partner")
      .forEach((value, index) => {
        if (partners.length < index + 1) {
          value.style.display = "none";
          return;
        } else {
          value.style.display = "flex";
        }
        value.href = partners[index].data.deeplink;
        let title_container = value.querySelector(".title");
        if (title_container) {
          title_container.textContent = partners[index].data.hostname;
        }
        let description_container = value.querySelector(".description");
        if (description_container) {
          description_container.textContent = partners[index].data.title;
        }
        let image_container = value.querySelector("img");
        if (image_container) {
          image_container.src = partners[index].data.imageUrl;
        }
      });

    // Add all Suggestions
    suggestions_container
      .querySelectorAll(".suggestion")
      .forEach((value, index) => {
        if (suggestions.length < index + 1) {
          value.style.display = "none";
          return;
        } else {
          value.style.display = "flex";
        }
        value.value = suggestions[index];
        let title_container = value.querySelector("span");
        if (title_container) {
          title_container.textContent = suggestions[index];
        }
      });
  }
})();
