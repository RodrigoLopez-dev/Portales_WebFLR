document.addEventListener("DOMContentLoaded", () => {
  const menuButton = document.querySelector(".site-menu-toggle");
  const mobileMenu = document.getElementById("siteMobileMenu");

  if (!menuButton || !mobileMenu) return;

  menuButton.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation();

    const isOpen = menuButton.getAttribute("aria-expanded") === "true";
    menuButton.setAttribute("aria-expanded", String(!isOpen));

    if (isOpen) {
      mobileMenu.setAttribute("hidden", "");
    } else {
      mobileMenu.removeAttribute("hidden");
    }
  });

  mobileMenu.addEventListener("click", (e) => {
    e.stopPropagation();
  });

  document.addEventListener("click", () => {
    mobileMenu.setAttribute("hidden", "");
    menuButton.setAttribute("aria-expanded", "false");
  });
});