// Função para aplicar a máscara de CEP
function aplicarMascaraCEP(input) {
  let valor = input.value;

  // Remove todos os caracteres não numéricos
  valor = valor.replace(/\D/g, "");

  // Aplica a máscara de CEP (#####-###)
  if (valor.length <= 5) {
      valor = valor.replace(/^(\d{5})(\d*)/, "$1-$2");
  } else {
      valor = valor.replace(/^(\d{5})(\d{1})(\d{1})(\d{3})/, "$1-$2$3$4");
  }

  // Atualiza o valor do campo com a máscara
  input.value = valor;
}

// Função para carregar o CEP com a máscara quando a página for carregada
window.onload = function() {
  let cepInput = document.getElementById("cep");
  aplicarMascaraCEP(cepInput);
}

// Buscar CEP
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
          document.getElementById("usuario_endereco").value =
            data.logradouro ?? "";
          document.getElementById("usuario_bairro").value = data.bairro ?? "";
          document.getElementById("usuario_cidade").value =
            data.localidade ?? "";
          document.getElementById("usuario_estado").value = data.uf ?? "";
        }
      })
      .catch((error) => {
        console.error("Erro:", error);
        alert("Ocorreu um erro ao buscar o CEP. Tente novamente mais tarde.");
      });
  });
});

// Máscara para telefone
const telefoneInput = document.getElementById("telefone");
const formulario = document.getElementById("form"); // Seleciona o formulário pelo ID específico

// Aplica a máscara automaticamente ao carregar a página
function aplicarMascaraTelefone(telefone) {
  let valor = telefone.replace(/\D/g, ""); // Remove tudo que não é número

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

  return valor;
}

// Aplica a máscara ao telefone ao carregar a página (caso já tenha um valor)
document.addEventListener("DOMContentLoaded", function () {
  telefoneInput.value = aplicarMascaraTelefone(telefoneInput.value);
});

// Evento de input (enquanto o usuário digita)
telefoneInput.addEventListener("input", function () {
  telefoneInput.value = aplicarMascaraTelefone(telefoneInput.value);
});

// Antes de enviar o formulário, remove a máscara do telefone
formulario.addEventListener("submit", function (event) {
  let telefoneSemMascara = telefoneInput.value.replace(/\D/g, ""); // Remove a máscara

  // Atribui o valor sem a máscara ao campo telefônico
  telefoneInput.value = telefoneSemMascara;
});

// Máscara para CPF/CNPJ
const form = document.getElementById("form");
const ehEmpresaCheckbox = document.getElementById("eh_empresa");
const usuario_documento_input = document.getElementById("usuario_documento");
const usuario_documento_input_Error = document.getElementById(
  "usuario_documento_Error"
);
document.getElementById('form').onsubmit = function() {
  let documento = document.getElementById('usuario_documento').value;

  // Remove a máscara
  documento = documento.replace(/\D/g, '');

  // Atualiza o valor do campo com o CPF ou CNPJ sem a máscara
  document.getElementById('usuario_documento').value = documento;
}

function mascaraDocumento(input) {
  let valor = input.value.replace(/\D/g, ''); // Remove qualquer caractere não numérico

  if (valor.length <= 11) {
      // Máscara CPF: ###.###.###-##
      input.value = valor.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
  } else {
      // Máscara CNPJ: ##.###.###/####-##
      input.value = valor.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
  }
}

window.onload = function() {
  // Aplica a máscara ao campo CPF/CNPJ ao carregar a página
  let documentoField = document.getElementById('usuario_documento');
  if (documentoField) {
      mascaraDocumento(documentoField);
  }
}
// Função para mostrar/ocultar os campos de preenchimento para organização no cadastro
function toggleOrganizacao(checkbox) {
  const orgFields = document.getElementById("organizacao_fields");
  orgFields.style.display = checkbox.checked ? "block" : "none";
}

// Remover atributo readonly antes do envio para o banco de dados
form.addEventListener("submit", function (event) {
  document.getElementById("endereco").removeAttribute("readonly");
  document.getElementById("usuario_bairro").removeAttribute("readonly");
  document.getElementById("usuario_cidade").removeAttribute("readonly");
  document.getElementById("usuario_estado").removeAttribute("readonly");
});

// Habilitar edição em editar_perfil.php
function habilitarEdicao() {
  // Seleciona todos os campos de texto, e-mails e checkboxes dentro do formulário
  const campos = document.querySelectorAll('#form input[type="text"], #form input[type="email"], #form input[type="checkbox"]');
  const btnBuscaCep = document.getElementById('buscarCep');
  // Torna os campos editáveis (ou habilita os checkboxes)
  campos.forEach(campo => {
    if (campo.type === 'checkbox') {
      campo.disabled = false; // Habilita checkboxes
    } else {
      campo.readOnly = false; // Torna campos de texto e e-mail editáveis
    }
  });
  btnBuscaCep.style.display = 'inline-block'; // Remove o estilo de display 'none'
  btnBuscaCep.removeAttribute('hidden'); // Remove o atributo 'hidden', caso esteja presente
  // Exibe o botão de salvar e oculta o botão de editar (se necessário)
  document.getElementById('salvarBtn').style.display = 'inline-block';
}

// Exibir um alerta caso nenhum checkbox estiver selecionado
function validarCheckboxes() {
  // Seleciona os checkboxes
  const checkboxes = document.querySelectorAll('#doador, #beneficiario');
  const algumSelecionado = Array.from(checkboxes).some(checkbox => checkbox.checked);

  if (!algumSelecionado) {
    alert('Por favor, selecione ao menos uma das opções: "Doar tintas" ou "Receber tintas".');
    return false; // Impede o envio do formulário
  }
  return true; // Permite o envio do formulário
}