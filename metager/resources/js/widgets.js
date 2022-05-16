function copyCode() {
  document.querySelector("#codesnippet").select();
  try {
    var successful = document.execCommand('copy');
    if (successful) {
      document.querySelector("#copyButton").classList.remove("btn-default");
      document.querySelector("#copyButton").classList.add("btn-success");
    } else {
      document.querySelector("#copyButton").classList.remove("btn-default");
      document.querySelector("#copyButton").classList.add("btn-danger");
    }
  } catch (err) {
    document.querySelector("#copyButton").classList.remove("btn-default");
    document.querySelector("#copyButton").classList.add("btn-danger");
  }
}
window.onload = function () {
  let copyButton = document.querySelector("#copyButton");
  if (copyButton != null) {
    copyButton.onclick = copyCode;
  }
};
