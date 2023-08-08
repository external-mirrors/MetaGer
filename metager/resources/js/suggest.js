/**
 * MetaGers basic suggestion module
 */
let suggestions = [];
let partners = [];
let query = "";
(() => {
  let searchbar_container = document.querySelector(".searchbar");
  if (!searchbar_container) {
    return;
  }
  let suggestions_container = searchbar_container.querySelector(".suggestions");
  if (!suggestions_container) {
    return;
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

  search_input.addEventListener("keyup", (e) => {
    if (e.key == "Escape") {
      e.target.blur();
    }
    suggest();
  });
  search_input.addEventListener("focusin", suggest);

  function suggest() {
    if (search_input.value.trim().length == 0) {
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
        console.log(response);
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
        console.log(response);
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
        value.href = "#";
        let title_container = value.querySelector("span");
        if (title_container) {
          title_container.textContent = suggestions[index];
        }
        value.dataset.query = suggestions[index];
      });
  }
  suggestions_container.querySelectorAll(".suggestion").forEach((element) => {
    element.addEventListener("click", (e) => {
      search_input.value = e.target.dataset.query;
      search_input.form.submit();
    });
  });
})();
