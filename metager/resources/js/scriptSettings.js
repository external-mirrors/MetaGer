document
  .querySelectorAll("#setting-form select, #filter-form select, #external-search-service select")
  .forEach((element) => {
    element.addEventListener("change", (e) => {
      e.target.form.submit();
    });
  });
