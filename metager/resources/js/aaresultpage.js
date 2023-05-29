let observer = new MutationObserver(validateDom);

let observeconfig = {
  subtree: true,
  childList: true,
  attributes: true,
  attributeOldValue: true,
};

function validateDom(mutationlist, observer) {
  for (let i = 0; i < mutationlist.length; i++) {
    let mutation = mutationlist[i];
    let target = mutation.target;
    if (!target.classList.contains("result")) {
      continue;
    }
    // Check style attribute
    let expectedStyle = "display: block !important;";
    let currentStyle = target.getAttribute("style");
    if (currentStyle == null || currentStyle != expectedStyle) {
      target.style = "display: block!important";
    }

    // Check other attributes
    let attributes = target.attributes;
    for (let j = 0; j < attributes.length; j++) {
      let attribute = attributes[j];
      if (attribute.name != "class" && attribute.name != "style") {
        target.attributes.removeNamedItem(attribute.name);
      }
    }
  }
}

observer.observe(document, observeconfig);
