// assets/javascripts/custom-select.js

document.addEventListener("DOMContentLoaded", () => {
  const customSelects = document.querySelectorAll("[data-custom-select]");

  // 1. Document click listener for outside clicks
  document.addEventListener("click", (e) => {
    customSelects.forEach((select, index) => {
      if (!select.contains(e.target)) {
        const dropdown = select.querySelector("[data-select-dropdown]");
        if (!dropdown.classList.contains("hidden")) {
          closeDropdown(select);
        }
      }
    });
  });

  customSelects.forEach((select, index) => {
    const toggleBtn = select.querySelector("[data-select-toggle]");
    const dropdown = select.querySelector("[data-select-dropdown]");
    const input = select.querySelector("[data-select-input]");
    const label = select.querySelector("[data-select-label]");
    const icon = select.querySelector("[data-select-icon]");
    const options = select.querySelectorAll("li[data-value]");

    // 2. Toggle button listener
    toggleBtn.addEventListener("click", (e) => {
      e.preventDefault();
      const isHidden = dropdown.classList.contains("hidden");

      customSelects.forEach((s, sIndex) => {
        if (s !== select) {
          const otherDropdown = s.querySelector("[data-select-dropdown]");
          if (!otherDropdown.classList.contains("hidden")) {
            closeDropdown(s);
          }
        }
      });

      if (isHidden) {
        openDropdown(dropdown, icon, index);
      } else {
        closeDropdown(select, index);
      }
    });

    // 3. Option selection listener
    options.forEach((option) => {
      option.addEventListener("click", (e) => {
        e.stopPropagation();

        const selectedValue = option.getAttribute("data-value");
        const selectedText = option.textContent.trim();

        input.value = selectedValue;
        label.textContent = selectedText;

        label.classList.remove("text-slate-400");
        label.classList.add("text-slate-900", "dark:text-white");

        closeDropdown(select, index);
      });
    });
  });

  // 4. Open/Close functions
  function openDropdown(dropdown, icon, index = "unknown") {
    clearTimeout(dropdown.dataset.timeoutId);

    dropdown.classList.remove("hidden");
    icon.classList.add("rotate-180");

    setTimeout(() => {
      dropdown.classList.remove("opacity-0", "scale-95");
      dropdown.classList.add("opacity-100", "scale-100");
    }, 10);
  }

  function closeDropdown(selectContainer, index = "unknown") {
    const dropdown = selectContainer.querySelector("[data-select-dropdown]");
    const icon = selectContainer.querySelector("[data-select-icon]");

    clearTimeout(dropdown.dataset.timeoutId);

    icon.classList.remove("rotate-180");
    dropdown.classList.remove("opacity-100", "scale-100");
    dropdown.classList.add("opacity-0", "scale-95");

    dropdown.dataset.timeoutId = setTimeout(() => {
      dropdown.classList.add("hidden");
    }, 200);
  }
});
