const detailText = document.querySelector("#detailText");
const cards = document.querySelectorAll(".item-card[data-detail]");
const settingsModal = document.querySelector("#settingsModal");
const profileModal = document.querySelector("#profileModal");
const settingsButtons = document.querySelectorAll("[data-open-settings]");
const dashboardShell = document.querySelector(".dashboard-shell");
const menuToggle = document.querySelector("[data-toggle-menu]");
const rotatingWord = document.querySelector("[data-rotating-word]");

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
            if (card.dataset.jobTitle) document.querySelector("#detailTitle")?.replaceChildren(card.dataset.jobTitle);
        }
    });
});

if (rotatingWord) {
    const words = ["futuro", "mundo", "universo", "avanço", "mercado", "amanhã"];
    let currentWord = 0;
    let changingWord = false;

    const showWord = (word, entering = false) => {
        rotatingWord.replaceChildren();

        [...word].forEach((character, index) => {
            const letter = document.createElement("span");
            const distanceFromCenter = index - (word.length - 1) / 2;

            letter.className = "palavra-letra";
            letter.textContent = character;
            letter.style.setProperty("--desvio-x", `${distanceFromCenter * 13}px`);
            letter.style.setProperty("--desvio-y", `${-10 - (index % 3) * 7}px`);
            letter.style.setProperty("--rotacao", `${distanceFromCenter * 12}deg`);
            letter.style.setProperty("--queda", `${28 + (index % 3) * 7}px`);
            letter.style.setProperty("--atraso", `${index * 38}ms`);

            if (entering) letter.classList.add("entrando");
            rotatingWord.append(letter);
        });

        if (entering) {
            requestAnimationFrame(() => requestAnimationFrame(() => {
                rotatingWord.querySelectorAll(".palavra-letra").forEach((letter) => letter.classList.remove("entrando"));
            }));
        }
    };

    showWord(words[currentWord]);

    window.setInterval(() => {
        if (changingWord) return;
        changingWord = true;
        rotatingWord.querySelectorAll(".palavra-letra").forEach((letter) => letter.classList.add("saindo"));

        window.setTimeout(() => {
            currentWord = (currentWord + 1) % words.length;
            showWord(words[currentWord], true);
            changingWord = false;
        }, 800);
    }, 3000);
}

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

const jobModal = document.querySelector("#jobModal");
const jobTitle = document.querySelector("[data-job-title-input]");
const jobDescription = document.querySelector("[data-job-description-input]");
const jobAction = document.querySelector("[data-job-action]");
const jobId = document.querySelector("[data-job-id]");
const jobModalTitle = document.querySelector("#jobModalTitle");
const jobSubmit = document.querySelector("[data-job-submit]");

document.querySelector("[data-open-job-create]")?.addEventListener("click", () => {
    jobAction.value = "create_job";
    jobId.value = "";
    jobTitle.value = "";
    jobDescription.value = "";
    jobModalTitle.textContent = "Criar vaga";
    jobSubmit.textContent = "Publicar vaga";
    jobModal?.showModal();
});

document.querySelectorAll("[data-open-job-edit]").forEach((button) => button.addEventListener("click", (event) => {
    event.stopPropagation();
    jobAction.value = "update_job";
    jobId.value = button.dataset.jobId;
    jobTitle.value = button.dataset.jobTitle;
    jobDescription.value = button.dataset.jobDescription;
    jobModalTitle.textContent = "Editar vaga";
    jobSubmit.textContent = "Salvar alteracoes";
    jobModal?.showModal();
}));

document.querySelectorAll("[data-delete-job]").forEach((button) => button.addEventListener("click", (event) => {
    if (!confirm("Excluir esta vaga? Esta acao nao pode ser desfeita.")) event.preventDefault();
}));

const searchInput = document.querySelector(".busca input[type='search']");
searchInput?.addEventListener("input", () => {
    const term = searchInput.value.trim().toLocaleLowerCase("pt-BR");
    document.querySelectorAll(".lista-area .item-card").forEach((card) => {
        card.hidden = term !== "" && !card.textContent.toLocaleLowerCase("pt-BR").includes(term);
    });
});

document.querySelector(".busca")?.addEventListener("submit", (event) => event.preventDefault());

if (new URLSearchParams(window.location.search).get("perfil") === "meu") profileModal?.showModal();
