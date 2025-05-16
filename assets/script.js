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
    const inputFile = document.getElementById("file");
  
    const fileBank = inputBankFile?.files[0];
    const file = inputFile?.files[0];

    // Validação básica
    if (!fileBank || !file) {
        alert("Selecione ambos os arquivos antes de continuar.");
        return;
    }

    // Validação de integridade dos arquivos
    if (fileBank.size === 0 || file.size === 0) {
        alert("Um ou ambos os arquivos estão vazios ou corrompidos. Por favor, selecione novamente.");
        return;
    }

    modal.style.display = "flex";
    cleanTerminal();
    whiteInTerminal("Iniciando comparação...");

    let formData = new FormData();
    formData.append("file", file);
    formData.append("nameColumnFile", document.getElementById("nameColumnFile").value);
    formData.append("csvSeparate", getChecked("csvSeparate"));

    whiteInTerminal("Enviando dados da arquivo...");

    fetch(`./core/routers/routerInterface.php?route=${getChecked("file_type")}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(response => {
        if (response.success) {

          whiteInTerminal("Dados do arquivo enviado com sucesso");
          whiteInTerminal("Enviando dados do extrato bancário...");
          formData = new FormData();
          formData.append("fileBank", fileBank);
          formData.append("pix_valid", getChecked("pix_valid"));
          formData.append("number_valid", getChecked("number_valid"));
          formData.append("compare", getChecked("compare"));
          formData.append("remove_barra", getChecked("remove_barra"));
          formData.append("remove_verify_digit", getChecked("remove_verify_digit"));
          formData.append("dataCSV", response.data);

          fetch(`./core/routers/routerInterface.php?route=${getChecked("bank")}`, {
            method: 'POST',
            body: formData
          })
          .then(data => data.json())
          .then(data => {
              if (data.success) {
                  whiteInTerminal("Dados enviado com sucesso");
                  whiteInTerminal("Comparação realizada com sucesso!");
                  dataReturn = data.data;
                  const button = document.getElementById("btnDownload");
                  button.style.backgroundColor = "#4caf50";
                  whiteInTerminal("Clique em Download para baixar!!!");
              } else {
                  alert(data.message || "Erro ao processar.");
                  whiteInTerminal("Erro ao enviar os arquivos");
              }
          })
          .catch(error => {
              // Captura erros como net::ERR_UPLOAD_FILE_CHANGED
              if (error instanceof TypeError && error.message.includes("ERR_UPLOAD_FILE_CHANGED")) {
                  alert("O arquivo foi alterado ou removido antes de ser enviado. Por favor, selecione novamente.");
                  whiteInTerminal("Erro: arquivo alterado durante o upload.");
              } else {
                  alert(data.message || "Erro ao processar.");
                  whiteInTerminal("Erro inesperado:");
                  whiteInTerminal(error);
              }
          });
        } else {
            alert(response.message || "Erro ao processar.");
            whiteInTerminal("Erro ao enviar o arquivo");
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
    if (!Array.isArray(dados) || dados.length === 0) {
      alert("Nenhum dado para exportar.");
      return;
    }
  
    let csv = '';
    
    // Verifica se é um array simples (ex: [1, 2, 3]) ou array de objetos
    if (typeof dados[0] !== 'object' || dados[0] === null) {
      // Array simples
      csv = 'Valor\n' + dados.map(item => `"${String(item).replace(/"/g, '""')}"`).join('\n');
    } else {
      // Array de objetos
      const firstValid = dados.find(d => typeof d === 'object' && d !== null && Object.keys(d).length > 0);
      if (!firstValid) {
        alert("Nenhum objeto válido para exportar.");
        return;
      }
  
      const header = Object.keys(firstValid);
      csv = header.join(',') + '\n';
  
      dados.forEach(item => {
        if (typeof item === 'object' && item !== null && Object.keys(item).length > 0) {
          const row = header.map(key => {
            const value = item[key] ?? ''; // Substitui undefined/null por vazio
            return `"${String(value).replace(/"/g, '""')}"`;
          }).join(',');
          csv += row + '\n';
        }
      });
    }
  
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", "resultado.csv");
  
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } 
});
