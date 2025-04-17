document.addEventListener("DOMContentLoaded", () => {
  const btnComparar = document.getElementById("btnComparar");
  const btnFecharModal = document.getElementById("btnFecharModal");
  const btnDownload = document.getElementById("btnDownload");
  const modal = document.getElementById("modal");
  const terminal = document.getElementById("terminal");

  btnComparar.addEventListener("click", validate);
  btnFecharModal.addEventListener("click", fecharModal);
  btnDownload.addEventListener("click", download);

  let dataReturn = null;

  function validate() {
    const inputBankFile = document.getElementById("extrato_bancario");
    const inputCSVFile = document.getElementById("csv_file");
  
    const fileBank = inputBankFile?.files[0];
    const fileCSV = inputCSVFile?.files[0];

    // Validação básica
    if (!fileBank || !fileCSV) {
        alert("Selecione ambos os arquivos antes de continuar.");
        return;
    }

    // Validação de integridade dos arquivos
    if (fileBank.size === 0 || fileCSV.size === 0) {
        alert("Um ou ambos os arquivos estão vazios ou corrompidos. Por favor, selecione novamente.");
        return;
    }

    modal.style.display = "flex";
    cleanTerminal();
    whiteInTerminal("Iniciando comparação...");

    const formData = new FormData();
    formData.append("fileBank", fileBank);
    formData.append("fileCSV", fileCSV);
    formData.append("pix_valid", getChecked("pix_valid"));
    formData.append("number_valid", getChecked("number_valid"));
    formData.append("compare", getChecked("compare"));
    formData.append("csvSeparate", getChecked("csvSeparate"));
    formData.append("remove_barra", getChecked("remove_barra"));
    formData.append("remove_verify_digit", getChecked("remove_verify_digit"));

    whiteInTerminal("Enviando dados...");

    fetch(`./core/routers/routerInterface.php?route=${getChecked("bank")}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(response => {
        if (response.success) {
            whiteInTerminal("Comparação realizada com sucesso!");
            dataReturn = response.data;
            const button = document.getElementById("btnDownload");
            button.style.backgroundColor = "#4caf50";
            whiteInTerminal("Clique em Download para baixar!!!");
        } else {
            alert(response.message || "Erro ao processar.");
            whiteInTerminal("Erro ao enviar os arquivos");
        }
    })
    .catch(error => {
        // Captura erros como net::ERR_UPLOAD_FILE_CHANGED
        if (error instanceof TypeError && error.message.includes("ERR_UPLOAD_FILE_CHANGED")) {
            alert("O arquivo foi alterado ou removido antes de ser enviado. Por favor, selecione novamente.");
            whiteInTerminal("Erro: arquivo alterado durante o upload.");
        } else {
            alert("Erro na comunicação com o servidor.");
            whiteInTerminal("Erro inesperado:");
            whiteInTerminal(error);
        }
    });
  }

  function getChecked(name) {
    const checked = document.querySelector(`input[name="${name}"]:checked`);
    return checked ? checked.value : null;
  }

  function fecharModal() {
    modal.style.display = "none";
  }

  function download() {
    whiteInTerminal("Iniciando download...");
    baixarCSV(dataReturn);
    whiteInTerminal("Baixado!!!");
  }

  function whiteInTerminal(texto) {
    terminal.textContent += `\n${texto}`;
    terminal.scrollTop = terminal.scrollHeight;
  }

  function cleanTerminal() {
    terminal.textContent = "";
    terminal.scrollTop = terminal.scrollHeight;
  }

  function baixarCSV(dados) {
    const header = ['Divergência'];

    // Monta o conteúdo CSV
    let csv = header.join(',') + '\n';
    dados.forEach(item => {
      csv += item + '\n';
    });

    // Cria um blob com o conteúdo CSV
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });

    // Cria um link para download
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", "resultado.csv");

    // Clica no link automaticamente
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
});
