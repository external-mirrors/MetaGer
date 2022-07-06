import MD5 from './lib/md5.js';
import t from './translations.js';

if (!('remove' in Element.prototype)) {
  Element.prototype.remove = function () {
    if (this.parentNode) {
      this.parentNode.removeChild(this);
    }
  };
}

if (typeof NodeList !== "undefined" && NodeList.prototype && !NodeList.prototype.forEach) {
  // Yes, there's really no need for `Object.defineProperty` here
  NodeList.prototype.forEach = Array.prototype.forEach;
}

/**
 * All results are stored in the global object 'results'
  */
let results = new Results();

document.addEventListener("DOMContentLoaded", (event) => {
  if (document.readyState == 'complete') {
    initResultSaver();
  } else {
    document.addEventListener("readystatechange", e => {
      if (document.readyState === 'complete') {
        initResultSaver();
      }
    });
  }
});

function initResultSaver() {
  // Add all saved results
  results.loadAllResults();
  // Sort all results
  results.sortResults();
  // Update the visualization
  results.updateResultPageInterface();
}

/**
 * Load all saved results and sort them
 * @param {String} sort The type of sorting function to call for these results
 */
function Results() {
  if (!localStorage) return;
  this.prefix = 'result_';
  this.sort = 'chronological';
  this.results = [];
}

/**
 * Adds a result to the list of results
 * @param {Result} result The result to add
 */
Results.prototype.addResult = function (result) {
  if (this.results.every(function (val) {
    return val.hash !== result.hash;
  })) {
    this.results.push(result);
  }
};

/**
 * Sorts all results according to the sort-type given with this.sort
 */
Results.prototype.sortResults = function () {
  if (this.sort === undefined) this.sort = 'chronological';
  switch (this.sort) {
    case 'chronological':
      this.results.sort(function (a, b) {
        if (a.added > b.added) return -1;
        if (a.added < b.added) return 1;
        return 0;
      });
      break;
    case 'rank':
      this.results.sort(function (a, b) {
        if (a.rank > b.rank) return -1;
        if (a.rank < b.rank) return 1;
        return 0;
      });
      break;
    case 'alphabetical': // by hostname
      this.results.sort(function (a, b) {
        if (b.hosterName > a.hosterName) return -1;
        if (b.hosterName < a.hosterName) return 1;
        return 0;
      });
      break;
  }
  return this;
};

/**
 * Load all Results from localstorage
 */
Results.prototype.loadAllResults = function () {
  // Iterate over all keys in the localstorage
  for (var i = 0; i < localStorage.length; i++) {
    // If the key starts with the prefix used for saved results
    var key = localStorage.key(i);
    if (key.indexOf(this.prefix) === 0) {
      // Remove the prefix
      key = key.substr(this.prefix.length);
      // Create the result for this key by loading it from localstorage
      var tmpResult = new Result(undefined, undefined, undefined, undefined, undefined, undefined, undefined, undefined, key);
      // Add the result to the list of results
      this.results.push(tmpResult);
    }
  }
};

/**
 * Delete all results from localstorage
 */
Results.prototype.deleteAllResults = function () {
  this.results = [];
  var keys = [];
  // Save all keys starting with the prefix used for saved results into the keys array
  for (var i = 0; i < localStorage.length; i++) {
    if (localStorage.key(i).indexOf(this.prefix) === 0) {
      var key = localStorage.key(i);
      keys.push(key);
    }
  }
  // Remove all keys saved in the keys array from localstorage
  keys.forEach(value => {
    localStorage.removeItem(value);
  });
};

/**
 * Update the result page to match the available results
 */
Results.prototype.updateResultPageInterface = function () {
  if (this.results.length === 0) {
    // If there are no saved-results left, remove the savedFoki element
    document.querySelectorAll('#savedFoki').forEach(element => {
      element.remove();
    });
    return;
  }
  if (document.querySelector('#savedFoki') == null || document.querySelector('#savedFoki').length === 0) {
    // If there is no savedFoki element yet, create it
    var template = document.createElement("div");
    template.innerHTML = '<div id="savedFoki">\
      <h1>' + t('result-saver.title') + '</h1>\
    </div>';
    var tabPanel = template.firstChild;
    document.querySelector('#additions-container').insertBefore(tabPanel, document.querySelector('#additions-container').firstChild);
  } else {
    // If there already is a savedFoki element, get it
    var tabPanel = document.querySelector("#savedFoki");
    tabPanel.innerHTML = "";
  }

  // Add the full savedFoki element to the tabPanel
  this.addToContainer(tabPanel);
};

/**
 * Create the savedFoki element and all of its content,
 * including the saver-options element and all saved results available
 * @param {HTML-Element} container The element to add the saved-results to
 */
Results.prototype.addToContainer = function (container) {
  // Create the saver-options element, which is a bar containing 
  // options for filtering, sorting and deleting all results
  var template = document.createElement("div");

  template.innerHTML = '<div id="saver-options">\
    <div class="saver-option">\
      <input style="font-family:, sans-serif;" class="form-control" type="text" placeholder="' + t('result-saver.filter') + '">\
    </div>\
    <div class="saver-option saver-option-sort">\
      <select class="form-control", sans-serif;">\
        <option value="chronological" , sans-serif;">🕓 ' + t('result-saver.sort.chronological') + '</option>\
        <option value="rank" style="font-family: , sans-serif;">🔢 ' + t('result-saver.sort.ranking') + '</option>\
        <option value="alphabetical" , sans-serif;">🔠 ' + t('result-saver.sort.alphabetical') + '</option>\
      </select>\
    </div>\
    <div class="saver-option saver-option-delete">\
      <button class="btn btn-danger btn-md" id="saver-options-delete-btn">\
      <img class= \"mg-icon mg-icon-inverted result-saver-icon\" src=\"/img/trashcan.svg\">\
        ' + t('result-saver.deleteAll') + '\
      </button>\
    </div>\
  </div>';
  var options = template.firstChild;

  // Set the initial value for the sorting select, based on this.sort
  options.querySelector("select").value = this.sort;

  // Add the saver-options element to the given container
  container.appendChild(options);

  /* ~~~ Filter ~~~ */
  // When the user is done typing into the filter input field,
  // Filter all results, only showing ones that contain the filer
  options.querySelectorAll("input").forEach(element => {
    // Get the entered filter
    var search = element.value;
    // Hide all results that do not contain the entered filter
    document.querySelectorAll('#savedFoki .saved-result-content').forEach(value => {
      // check for filter in all of the elements html-content
      var html = value.innerHTML;
      if (html.toLowerCase().indexOf(search.toLowerCase()) === -1) {
        // hide entire result block
        value.parentNode.classList.add('hidden');
      } else {
        // show entire result block
        value.parentNode.classList.remove("hidden");
      }
    });
  });

  /* ~~~ Sort ~~~ */
  // When the sorting select value is changed, 
  // Sort all results with the selected sorting function and update their appearance
  options.querySelectorAll("select").forEach(element => {
    element.onchange = (e) => {
      var sort = element.value;
      results.sort = sort;
      results.sortResults(sort).updateResultPageInterface();
    };
  });

  /* ~~~ Delete ~~~ */
  // When the delete button is clicked,
  // Delete all results and update their appearance
  options.querySelectorAll('#saver-options-delete-btn').forEach(element => {
    element.onclick = (e) => {
      results.deleteAllResults();
      results.updateResultPageInterface();
    }
  });

  // Append all results available
  this.results.forEach(result => {
    container.appendChild(result.toHtml());
  });
};

/**
 * Creates a result object
 * @param {String} title The title of this result
 * @param {String} link The link to this result
 * @param {String} anzeigeLink The displayed link
 * @param {String} description The description of this result
 * @param {String} anonym The link to open this result anonymously
 * @param {int} rank The rank of this result
 * @param {int} hash The hash value for this result
 */
function Result(title, link, hosterName, hosterLink, anzeigeLink, description, anonym, index, hash) {
  // Set prefix for localstorage
  this.prefix = 'result_';

  if (hash === null || hash === undefined) {
    // Calculate the hash value of this result
    hash = MD5(title + link + hosterName + hosterLink + anzeigeLink + description + anonym);
  }

  this.hash = hash;

  // Try to load the result, if there was none create it
  if (!this.load()) {
    // Save all important data
    this.title = title;
    this.link = link;
    this.hosterName = hosterName;
    this.hosterLink = hosterLink;
    this.anzeigeLink = anzeigeLink;
    this.description = description;
    this.anonym = anonym;
    this.index = index;
    this.rank = index;
    this.added = new Date().getTime();

    // Save this result to localstorage
    this.save();
  }
}

/**
 * Load this result from local storage
 * The key used equals 'prefix + hash' of this result
 */
Result.prototype.load = function () {
  if (!localStorage) return false;

  // Try to load from local storage
  var encoded = localStorage.getItem(this.prefix + this.hash);
  if (encoded === null) return false;

  // Decode the base64 result into a normal string, then json
  var decoded = b64DecodeUnicode(encoded);
  var result = JSON.parse(decoded);

  // Load all important data
  this.title = result.title;
  this.link = result.link;
  this.anzeigeLink = result.anzeigeLink;
  this.hosterName = result.hosterName;
  this.hosterLink = result.hosterLink;
  this.anonym = result.anonym;
  this.description = result.description;
  this.added = result.added;
  this.index = result.index;
  this.rank = result.rank;

  return true;
};

/**
 * Save the data of this result into localstorage
 * The key used equals 'prefix + hash' of this result
 */
Result.prototype.save = function () {
  if (!localStorage) return false;
  // Save all important data
  var result = {
    title: this.title,
    link: this.link,
    anzeigeLink: this.anzeigeLink,
    hosterName: this.hosterName,
    hosterLink: this.hosterLink,
    anonym: this.anonym,
    description: this.description,
    added: this.added,
    index: this.index,
    rank: this.rank
  };

  // Encode the result object into a string, then into base64
  result = JSON.stringify(result);
  result = b64EncodeUnicode(result);

  // Save the result string into local storage
  // The key used equals 'prefix + hash' of this result
  localStorage.setItem(this.prefix + this.hash, result);

  return true;
};

/**
 * Convert a string into base64 format
 * @param {String} str The string to convert
 */
function b64EncodeUnicode(str) {
  return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function (match, p1) {
    return String.fromCharCode('0x' + p1);
  }));
}

/**
 * Convert a base64 string into normal format
 * @param {String} str The string to convert
 */
function b64DecodeUnicode(str) {
  return decodeURIComponent(Array.prototype.map.call(atob(str), function (c) {
    return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
  }).join(''));
}

Result.prototype.setIndex = function (index) {
  this.index = index;
};

/**
 * Remove this result from localstorage and refresh the view
 */
Result.prototype.remove = function () {
  localStorage.removeItem(this.prefix + this.hash);
  var hash = this.hash;
  results.results.splice(results.results.findIndex(function (result) {
    return result.hash === hash;
  }), 1);
  results.updateResultPageInterface();
};

/**
 * Converts this result object into an html element
 * @returns {HTML-Element} The converted HTML-Element
 */
Result.prototype.toHtml = function () {
  // Create the saved-result element
  var template = document.createElement("div");
  template.innerHTML = '<div class="saved-result result" data-count="' + this.index + '">\
    <div class="saved-result-remover remover" title="' + t('result-saver.delete') + '">\
    <img class= \"mg-icon result-saver-icon\" src=\"/img/trashcan.svg\">\
    </div>\
    <div class="saved-result-content">\
      <div class="result-header">\
        <div class="result-headline">\
          <h2 class="result-title">\
            <a href="' + this.link + '" target="_blank" data-count="1" rel="noopener">\
              ' + this.title + '\
            </a>\
          </h2>\
          <a class="result-hoster" href="' + this.hosterLink + '" target="_blank" data-count="1" rel="noopener">\
            ' + this.hosterName + '\
          </a>\
        </div>\
        <a class="result-link" href="' + this.link + '" target="_blank" rel="noopener">\
          ' + this.anzeigeLink + '\
        </a>\
      <div class="result-body">\
        <div class="description">' + this.description + '</div>\
      </div>\
      <div class="result-footer">\
        <a class="result-open" href="' + this.link + '" target="_self" rel="noopener">\
          ' + t('result-saver.save.this') + '\
        </a>\
        <a class="result-open" href="' + this.link + '" target="_blank" rel="noopener">\
          ' + t('result-saver.save.newtab') + '\
        </a>\
        <a class="result-open-proxy" href="' + this.anonym + '" target="_blank" rel="noopener" title="Der Link wird anonymisiert geöffnet. Ihre Daten werden nicht zum Zielserver übertragen. Möglicherweise funktionieren manche Webseiten nicht wie gewohnt.">\
          ' + t('result-saver.save.anonymous') + '\
        </a>\
      </div>\
    </div>\
  </div>';

  var result = template.firstChild;

  // Add a click listener to the remover, that removes the result on click
  var caller = this;
  result.querySelectorAll(".remover").forEach(element => {
    element.onclick = (e) => {
      caller.remove();
    };
  });

  return result;
};

/**
 * Saves the result at the given index
 * @param {int} index The index of the result to save
 */
export default function resultSaver(index) {
  // Remember the original result element
  var original = document.querySelector('.result[data-count="' + index + '"]');

  // Read the necessary data from the result html
  var title = document.querySelector('.result[data-count="' + index + '"] .result-title a').innerHTML.trim();
  var link = document.querySelector('.result[data-count="' + index + '"] .result-title a').href.trim();
  var hosterName = document.querySelector('.result[data-count="' + index + '"] .result-subheadline > a').title.trim();
  var hosterLink = document.querySelector('.result[data-count="' + index + '"] .result-hoster').href;
  hosterLink = hosterLink ? hosterLink.trim() : "#";
  var anzeigeLink = document.querySelector('.result[data-count="' + index + '"] .result-link').innerHTML.trim();
  var description = document.querySelector('.result[data-count="' + index + '"] .result-description').innerHTML.trim();
  var anonym = document.querySelector('.result[data-count="' + index + '"] .result-open-proxy').href.trim();

  // Create the result object
  var result = new Result(title, link, hosterName, hosterLink, anzeigeLink, description, anonym, index, null);

  // Add new result to results
  results.addResult(result);

  // Sort results
  results.sortResults();

  // Update the saved results
  results.updateResultPageInterface();

  // Animate the result transfer to the saved results
  // var transferTarget = $('.saved-result[data-count=' + index + ']');
  // if (original.length > 0 && transferTarget.length > 0) {
  //   $(original).transfer({ to: transferTarget, duration: 1000 });
  // }
}
