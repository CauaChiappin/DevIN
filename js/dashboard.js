const detailText = document.querySelector("#detailText");
const cards = document.querySelectorAll(".item-card[data-detail]");
const settingsModal = document.querySelector("#settingsModal");
const profileModal = document.querySelector("#profileModal");
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

document.querySelector("[data-open-profile]")?.addEventListener("click", () => {
    document.querySelector(".perfil-dropdown")?.removeAttribute("open");
    profileModal?.showModal();
});

document.querySelector(".profile-photo input")?.addEventListener("change", (event) => {
    const [photo] = event.target.files;

    if (!photo) return;

    const previewUrl = URL.createObjectURL(photo);
    document.querySelectorAll(".current-user-avatar").forEach((preview) => {
        preview.innerHTML = "";
        const image = document.createElement("img");
        image.src = previewUrl;
        image.alt = "Prévia da foto de perfil";
        preview.appendChild(image);
    });
});

document.querySelectorAll("[data-close-modal]").forEach((button) => {
    button.addEventListener("click", () => button.closest("dialog")?.close());
});



document.querySelectorAll('[data-confirm-logout]').forEach((link) => {
    link.addEventListener('click', (event) => {
        const message = link.dataset.confirmLogout || 'Tem certeza que deseja sair?';
        if (!window.confirm(message)) {
            event.preventDefault();
        }
    });
});

document.querySelector("[data-delete-account]")?.addEventListener("click", (event) => {
    if (!confirm("Tem certeza? Esta ação exclui sua conta permanentemente.")) event.preventDefault();
});

if (new URLSearchParams(window.location.search).get("perfil") === "meu") profileModal?.showModal();
