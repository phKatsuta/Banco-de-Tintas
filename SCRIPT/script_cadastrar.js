// Máscara para CPF/CNPJ
const form = document.getElementById("cadastroForm");
const ehEmpresaCheckbox = document.getElementById("eh_empresa");
const usuario_documento_input = document.getElementById("usuario_documento");
const usuario_documento_input_Error = document.getElementById(
  "usuario_documento_Error"
);

usuario_documento_input.addEventListener("input", function () {
  let valor = usuario_documento_input.value.replace(/\D/g, ""); // Remove tudo que não é número

  if (valor.length > 11) {
    // Formato de CNPJ: ##.###.###/####-##
    valor = valor.replace(
      /^(\d{2})(\d{3})(\d{3})(\d{4})(\d{0,2})$/,
      "$1.$2.$3/$4-$5"
    );
  } else if (valor.length > 9) {
    // Formato de CPF: ###.###.###-##
    valor = valor.replace(/^(\d{3})(\d{3})(\d{3})(\d{0,2})$/, "$1.$2.$3-$4");
  } else if (valor.length > 6) {
    valor = valor.replace(/^(\d{3})(\d{3})(\d{0,3})$/, "$1.$2.$3");
  } else if (valor.length > 3) {
    valor = valor.replace(/^(\d{3})(\d{0,3})$/, "$1.$2");
  }

  cpfCnpjInput.value = valor;
});

form.addEventListener("submit", function (e) {
  // Verifica se o campo CPF/CNPJ é obrigatório
  if (
    ehEmpresaCheckbox.checked &&
    usuario_documento_input.value.trim() === ""
  ) {
    e.preventDefault(); // Impede o envio do formulário
    usuario_documento_input_Error.style.display = "inline"; // Mostra a mensagem de erro
  } else {
    usuario_documento_input_Error.style.display = "none"; // Esconde a mensagem de erro
  }
});

// Máscara para telefone
const telefoneInput = document.getElementById("telefone");
telefoneInput.addEventListener("input", function () {
  let valor = telefoneInput.value.replace(/\D/g, ""); // Remove tudo que não é número

  if (valor.length > 10) {
    // Formato com 9 dígitos no telefone
    valor = valor.replace(/^(\d{2})(\d{5})(\d{4})$/, "($1) $2-$3");
  } else if (valor.length > 6) {
    // Formato com 8 dígitos no telefone
    valor = valor.replace(/^(\d{2})(\d{4})(\d{0,4})$/, "($1) $2-$3");
  } else if (valor.length > 2) {
    // Formato com DDD
    valor = valor.replace(/^(\d{2})(\d{0,4})$/, "($1) $2");
  } else if (valor.length > 0) {
    // Apenas o DDD
    valor = valor.replace(/^(\d{0,2})$/, "($1");
  }

  telefoneInput.value = valor;
});

// Função para mostrar/ocultar os campos de preenchimento para organização
function toggleOrganizacao(checkbox) {
  const orgFields = document.getElementById("organizacao_fields");
  orgFields.style.display = checkbox.checked ? "block" : "none";
}

document.addEventListener("DOMContentLoaded", () => {
  document.getElementById("buscarCep").addEventListener("click", () => {
    const cep = document.getElementById("cep").value.replace(/\D/g, "");

    if (!/^\d{8}$/.test(cep)) {
      alert("CEP inválido. Por favor, insira um CEP com 8 dígitos.");
      return;
    }

    fetch(`../includes/via_cep.php?cep=${cep}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.erro) {
          alert("CEP não encontrado.");
        } else {
          document.getElementById("usuario_endereco").value = data.logradouro ?? "";
          document.getElementById("usuario_bairro").value = data.bairro ?? "";
          document.getElementById("usuario_cidade").value = data.localidade ?? "";
          document.getElementById("usuario_estado").value = data.uf ?? "";
        }
      })
      .catch((error) => {
        console.error("Erro:", error);
        alert("Ocorreu um erro ao buscar o CEP. Tente novamente mais tarde.");
      });
  });
});

// Remover atributo readonly antes do envio para o banco de dados
form.addEventListener("submit", function (event) {
  document.getElementById("endereco").removeAttribute("readonly");
  document.getElementById("bairro").removeAttribute("readonly");
  document.getElementById("cidade").removeAttribute("readonly");
  document.getElementById("estado").removeAttribute("readonly");
});
