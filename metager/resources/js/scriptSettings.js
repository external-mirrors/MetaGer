document
  .querySelectorAll("#setting-form select, #filter-form select")
  .forEach((element) => {
    element.addEventListener("change", (e) => {
      e.target.form.submit();
    });
  });
