import { getToken, putToken } from "./messaging";

/**
 * MetaGers basic suggestion module
 */
export function initializeSuggestions() {

  if (!document.querySelector("meta[name=suggestions-enabled]")) {
    return;
  }

  let active = true;

  let suggestions = [];
  let query = "";
  let searchbar_container = document.querySelector(".searchbar");
  let on_startpage = document.querySelector("#searchForm .startpage-searchbar") != null;

  let suggest_id = crypto.randomUUID();
  let tokens = null;
  let last_cost = 0;
  let counter = 0;

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

  // Update cost for suggestion requests
  (() => {
    fetch(suggestion_url + "/cost").then(response => response.json()).then(async response => {
      last_cost = response.tokencost;
      if (document.activeElement == search_input) {
        await getAnonymousTokens(last_cost);
      }
    })
  })();

  search_input.addEventListener("keyup", (e) => {
    if (!active) return;
    let ignored_keys = ["ArrowDown", "ArrowUp", "ArrowLeft", "ArrowRight"];
    moveFocus(e.key);
    if (e.key == "Escape") {
      e.stopPropagation();
      e.target.blur();
    } else if (!ignored_keys.includes(e.key)) {
      suggest();
    }
  });
  search_input.addEventListener("paste", e => {
    if (!active) return;
    e.preventDefault();
    search_input.value = e.clipboardData.getData("text");
    suggest();
  });
  search_input.addEventListener("focusin", e => {
    active = true;
    e.preventDefault();
    suggest();
  });
  search_input.addEventListener("blur", e => {
    active = false;
    cancelSuggest();
    setTimeout(() => {
      if (document.activeElement != search_input) {
        searchbar_container.dataset.suggest = "inactive";
      }
    }, 250);
  });
  search_input.form.addEventListener("submit", e => {
    active = false;
    e.preventDefault();
    let form = e.target;
    cancelSuggest().then(() => form.submit());
  })

  async function suggest(iteration = 1) {
    if (iteration > 2 || navigator.webdriver) {
      suggestions = [];
      updateSuggestions();
      return;
    }
    let token_header = null;
    let decitoken_header = null;
    let cost = last_cost;
    if (cost > 0) {
      await getAnonymousTokens(cost);
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


    counter += 1;
    return fetch(suggestion_url + "?query=" + encodeURIComponent(search_input.value.trim()), {
      method: "POST",
      headers: {
        Accept: "application/json",
        tokens: JSON.stringify(token_header),
        decitokens: JSON.stringify(decitoken_header),
        id: suggest_id,
        number: counter
      }
    })
      .then(async (response) => {
        let status = response.status;
        let json_response = await response.json();

        switch (+status) {
          case 200:
            await recycleTokens({ tokens: token_header, decitokens: decitoken_header }, json_response);
            query = search_input.value.trim();
            suggestions = json_response[1];
            updateSuggestions();
            return putAnonymousTokens();
          case 423:
            break;
          case 402:
            await recycleTokens({ tokens: token_header, decitokens: decitoken_header }, json_response);
            last_cost = json_response.cost;
            return suggest(iteration + 1);
        }
        //return response.json()
      })
      .catch(reason => {
        suggestions = [];
        updateSuggestions();
      });
  }

  async function cancelSuggest() {
    return putAnonymousTokens().then(() => {
      return fetch(suggestion_url + "/cancel", {
        headers: {
          id: suggest_id,
        }
      })
    });
  }

  /**
   * Replaces locally stored tokens with the received server resposne
   * Those tokens will be checked valid. All other local tokens will be invalidated
   * @param {*} json_response 
   * @returns 
   */
  async function recycleTokens(sent_tokens, json_response) {
    let new_tokens = { tokens: [], decitokens: [] };
    // Keep all tokens that were not sent 
    if (sent_tokens.tokens != null)
      new_tokens.tokens = tokens.tokens.filter(x => !sent_tokens.tokens.includes(x));
    if (sent_tokens.decitokens != null)
      new_tokens.decitokens = tokens.decitokens.filter(x => !sent_tokens.decitokens.includes(x));

    // Keep all tokens returned by the server
    if (json_response.hasOwnProperty("tokens")) {
      json_response.tokens.forEach(token => {
        new_tokens.tokens.push(token);
      });
    }
    if (json_response.hasOwnProperty("decitokens")) {
      json_response.decitokens.forEach(token => {
        new_tokens.decitokens.push(token);
      });
    }
    tokens = new_tokens;
  }

  /**
   * Returns unused anonymous Tokens to the extension
   */
  async function putAnonymousTokens() {
    let recycleTokens = {
      tokens: {
        tokens: [],
        decitokens: []
      }
    };
    if (tokens == null) return;
    tokens.tokens.forEach(token => {
      recycleTokens.tokens.tokens.push(token);
    });
    tokens.decitokens.forEach(token => {
      recycleTokens.tokens.decitokens.push(token);
    });

    if (recycleTokens.tokens.tokens.length > 0 || recycleTokens.tokens.decitokens.length > 0) {
      tokens = null;
      return putToken(recycleTokens);
    }
  }

  async function getAnonymousTokens(cost) {
    let current_tokencount = 0;
    if (tokens != null) {
      tokens.tokens.forEach(token => {
        current_tokencount += 1;
      });
      tokens.decitokens.forEach(token => {
        current_tokencount += 0.1;
      });
    }

    if (cost <= current_tokencount) return;

    return getToken({
      cost: cost,
      missing: Math.max(cost - current_tokencount, 0),
      tokens: {
        tokens: [],
        decitokens: [],
      }
    }).then(newtokens => {
      if (tokens == null) tokens = { tokens: [], decitokens: [] };

      newtokens.tokens.tokens.forEach(token => {
        tokens.tokens.push(token);
      });

      newtokens.tokens.decitokens.forEach(token => {
        tokens.decitokens.push(token);
      });
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

  function moveFocus(key) {
    if (key != "ArrowUp" && key != "ArrowDown") {
      suggestions_container.querySelectorAll(".suggestion").forEach((element, index) => {
        if (element.classList.contains("active"))
          element.classList.remove("active");
      });
      return;
    }
    let down = key == "ArrowDown";
    let focus_number = -1;
    let max_number = suggestions_container.querySelectorAll(".suggestion").length;
    suggestions_container.querySelectorAll(".suggestion").forEach((element, index) => {
      if (element.classList.contains("active")) {
        focus_number = index;
        element.classList.remove("active");
      }
    });
    if (down) {
      if (focus_number >= (max_number - 1)) {
        focus_number = 0;
      } else {
        focus_number++;
      }
    } else {
      if (focus_number <= -1) {
        focus_number = max_number - 1;
      } else {
        focus_number--;
      }
    }
    if (focus_number == -1) {
      search_input.value = query;
    } else {
      let element = suggestions_container.querySelector(".suggestion:nth-child(" + (focus_number + 1) + ")");
      element.classList.add("active");
      search_input.value = element.querySelector("span").textContent;
    }
  }
}
