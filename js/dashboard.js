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

// Escuta somente o campo de arquivo usado pela foto de perfil.
document.querySelector(".profile-photo input[type='file']")?.addEventListener("change", (event) => {
    // Lê o primeiro arquivo de forma segura; evita erro se não houver seleção.
    const photo = event.currentTarget.files?.[0];

    // Se a pessoa fechar o seletor sem escolher arquivo, não há nada para fazer.
    if (!photo) return;

    // Repete no navegador os formatos permitidos no servidor antes de criar a prévia.
    if (!/^image\/(jpeg|png|webp)$/.test(photo.type)) {
        // Limpa a seleção inválida para ela não ser enviada pelo formulário.
        event.currentTarget.value = "";
        alert("Selecione uma imagem JPG, PNG ou WEBP.");
        return;
    }

    // Cria uma URL temporária para exibir a imagem escolhida sem enviá-la ainda.
    const previewUrl = URL.createObjectURL(photo);
    // Atualiza todos os avatares da tela: modal, menu lateral e cartão de perfil.
    document.querySelectorAll(".current-user-avatar").forEach((preview) => {
        // Remove a imagem anterior e injeta uma única tag img no avatar.
        preview.replaceChildren();
        // Cria a tag <img> pelo JavaScript em vez de montar HTML em texto.
        const image = document.createElement("img");
        // Define a URL temporária da imagem que acabou de ser selecionada.
        image.src = previewUrl;
        // Texto alternativo usado por leitores de tela e se a imagem falhar.
        image.alt = "Prévia da foto de perfil";
        // Coloca a nova imagem dentro do avatar.
        preview.appendChild(image);
    });
});

document.querySelectorAll("[data-close-modal]").forEach((button) => {
    button.addEventListener("click", () => button.closest("dialog")?.close());
});

document.querySelector("[data-delete-account]")?.addEventListener("click", (event) => {
    if (!confirm("Tem certeza? Esta ação exclui sua conta permanentemente.")) event.preventDefault();
});

if (new URLSearchParams(window.location.search).get("perfil") === "meu") profileModal?.showModal();
