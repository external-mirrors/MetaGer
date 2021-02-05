/**
 * Flag ctrlInfo is used for initial display of the navigation box
 */
var ctrlInfo = false;
document.addEventListener("DOMContentLoaded", (event) => {
    // Add entry point for tabbing to the first result
    document.querySelectorAll('.result, .ad')[0].setAttribute("id", "results-entry");
    // Initially focus the searchbar
});

/**
 * Simulate a click on enter keypress when focused on labels
 */
document.querySelectorAll('label').forEach((element) => {
    element.onkeydown = (e) => {
        if (e.keyCode == '13') {
            e.srcElement.click();
        }
    }
});

/**
 * Handles tab keypress and escape keypress
 */
document.onkeydown = (e) => {
    e = e || window.event;
    // On first tab keypress there is special behaviour and the ctrlInfo flag is set
    if (!ctrlInfo && e.keyCode == '9') {
        focusNavBox();
        e.preventDefault();
        ctrlInfo = true;
    } else if (e.keyCode == '27') {
        escKeyPressed();
    }
};


/**
 * Shows the navigation box and focuses the first <a> tag 
 */
function focusNavBox() {
    document.querySelector("#keyboard-nav-info").style.display = "inherit";
    document.querySelectorAll("#keyboard-nav-info a")[0].focus();
}

/**
 * Focuses the navigation box and unchecks all checkboxes
 */
function escKeyPressed() {
    focusNavBox();
    document.querySelectorAll('input[type="checkbox"]').forEach((element) => {
        element.checked = false;
    });
}

/**
 * Focuses the first <a> tag of the first result 
 */
function focusResults() {
    document.querySelector('#results-entry .result-title a').focus();
}

/**
 * Focuses the first <a> tag of the focus options
 */
function focusFoki() {
    document.querySelector("#foki a").focus();
}

/**
 * Focuses the search settings
 */
function focusSettings() {
    document.querySelector('#settings a').focus();
}

/**
 * Focuses the first <tag> of the sidebar
 */
function focusNavigation() {
    document.querySelector("#sidebarToggle").checked = true;
    document.querySelector(".sidebar-list a").focus();
}