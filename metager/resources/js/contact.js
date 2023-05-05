if (navigator.webdriver) {
    let pcsrf_element = document.querySelector("input[name=pcsrf]");

    pcsrf_element.value = pcsrf_element.value.substr(0, pcsrf_element.value.length - 1)
}