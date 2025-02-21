import { getToken, putToken } from "./messaging";

/**
 * MetaGers basic suggestion module
 */
export function initializeSuggestions() {
  let suggestions = [];
  let query = "";
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

  search_input.addEventListener("keyup", (e) => {
    if (e.key == "Escape") {
      e.stopPropagation();
      e.target.blur();
    } else {
      suggest();
    }
  });
  search_input.addEventListener("paste", e => {
    e.preventDefault();
    search_input.value = e.clipboardData.getData("text");
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

  async function suggest(cost = 0, iteration = 1) {
    console.log(cost);

    if (iteration > 2 || navigator.webdriver) {
      suggestions = [];
      updateSuggestions();
      return;
    }
    let token_header = null;
    let decitoken_header = null;
    if (cost > 0) {
      let tokens = await getAnonymousTokens(cost);
      token_header = [];
      let index = 0;
      while (cost >= 1) {
        if (tokens.tokens.length >= (index + 1)) {
          token_header[index] = tokens.tokens[index];
          cost -= 1;
        } else {
          break;
        }
        index++;
      }
      decitoken_header = [];
      index = 0;
      while (cost > 0) {
        if (tokens.decitokens.length >= (index + 1)) {
          decitoken_header[index] = tokens.decitokens[index];
          cost -= 0.1;
        }
        index++;
      }
    }
    if (search_input.value.trim() == query) {
      updateSuggestions();
      return;
    }

    return fetch(suggestion_url + "?query=" + encodeURIComponent(search_input.value.trim()), {
      method: "GET",
      headers: {
        Accept: "application/json",
        tokens: JSON.stringify(token_header),
        decitokens: JSON.stringify(decitoken_header),
      }
    })
      .then(async (response) => {
        let status = response.status;
        let json_response = await response.json();
        await recycleTokens(json_response);

        console.log(status, status == 402);
        switch (+status) {
          case 200:
            query = search_input.value.trim();
            suggestions = json_response[1];
            updateSuggestions();
            return;
          case 423:
            break;
          case 402:
            console.log(json_response);
            return suggest(json_response.cost, iteration + 1);
        }
        //return response.json()
      })
      .catch(reason => {
        suggestions = [];
        updateSuggestions();
      });
  }

  async function recycleTokens(json_response) {
    let recycleTokens = {
      tokens: {
        tokens: [],
        decitokens: []
      }
    };
    if (json_response.hasOwnProperty("tokens")) {
      recycleTokens.tokens.tokens = json_response.tokens;
    }
    if (json_response.hasOwnProperty("decitokens")) {
      recycleTokens.tokens.decitokens = json_response.decitokens;
    }

    if (recycleTokens.tokens.tokens.length > 0 || recycleTokens.tokens.decitokens.length > 0) {
      return putToken(recycleTokens);
    }
  }

  async function getAnonymousTokens(cost) {
    return getToken({
      cost: cost,
      missing: cost,
      tokens: {
        tokens: [],
        decitokens: [],
      }
    }).then(newtokens => {
      return newtokens.tokens;
    })
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
