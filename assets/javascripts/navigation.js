document.addEventListener("DOMContentLoaded", () => {
  const navLinks = document.querySelectorAll("nav a");

  const domain = "/studium_reservation_management";

  navLinks.forEach((link) => {
    let href = link.getAttribute("href");

    // Only modify if it's a relative path (not absolute, not a query, not a hash)
    if (
      href &&
      !href.startsWith("http") &&
      !href.startsWith("/") &&
      !href.startsWith("?") &&
      !href.startsWith("#")
    ) {
      link.setAttribute("href", domain + "/" + href);
    }
  });
});
