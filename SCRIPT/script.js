document.addEventListener("DOMContentLoaded", function () {
  console.log("Página carregada com sucesso!");
});

// Função para abrir o modal
function openModal() {
  const modal = document.getElementById("loginModal");
  modal.style.display = "flex";
  modal.setAttribute("aria-hidden", "false");
}

// Função para fechar o modal
function closeModal() {
  const modal = document.getElementById("loginModal");
  modal.style.display = "none";
  modal.setAttribute("aria-hidden", "true");
}

// Fecha o modal ao clicar fora do conteúdo
window.onclick = function (event) {
  const modal = document.getElementById("loginModal");
  if (event.target === modal) {
    closeModal();
  }
};
