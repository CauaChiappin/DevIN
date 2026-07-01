const detailText = document.querySelector("#detailText");
const cards = document.querySelectorAll(".item-card[data-detail]");
const settingsModal = document.querySelector("#settingsModal");
const settingsButtons = document.querySelectorAll("[data-open-settings]");
const dashboardShell = document.querySelector(".dashboard-shell");
const menuToggle = document.querySelector("[data-toggle-menu]");

if (dashboardShell && menuToggle) {
    const menuFechado = localStorage.getItem("devin-menu-fechado") === "true";

    dashboardShell.classList.toggle("menu-fechado", menuFechado);
    menuToggle.setAttribute("aria-expanded", String(!menuFechado));

    menuToggle.addEventListener("click", () => {
        const fechado = dashboardShell.classList.toggle("menu-fechado");

        menuToggle.setAttribute("aria-expanded", String(!fechado));
        localStorage.setItem("devin-menu-fechado", String(fechado));
    });
}

cards.forEach((card) => {
    card.addEventListener("click", () => {
        cards.forEach((item) => item.classList.remove("selecionado"));
        card.classList.add("selecionado");

        if (detailText) {
            detailText.textContent = card.dataset.detail;
        }
    });
});

settingsButtons.forEach((button) => {
    button.addEventListener("click", () => {
        if (settingsModal && typeof settingsModal.showModal === "function") {
            settingsModal.showModal();
        }
    });
});
