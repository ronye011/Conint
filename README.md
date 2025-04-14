# 📁 FCSV

## 📄 Descrição

O **FCSV** é um sistema que permite comparar as movimentações de um arquivo **CSV** com os números **"Nosso Número"** ou **"Seu Número"** extraídos de um extrato bancário.  
Este sistema é útil para validar e reconciliar transações financeiras de forma prática e eficiente.

---

## ✨ Funcionalidades

- ✅ Comparação entre arquivos CSV e extratos bancários
- 🔍 Suporte para validação de **PIX**
- 🔄 Opções de comparação por **"Seu Número"** ou **"Nosso Número"**
- 📌 Suporte para diferentes separadores de CSV (vírgula ou ponto e vírgula)
- 📥 Geração de um novo arquivo CSV com os registros **não encontrados**

---

## ⚙️ Pré-requisitos

- PHP 7.4 ou superior
- Apache 2.4 ou superior
- Git

---

## 📦 Instalação

### 1. Baixe o pacote mais recente do repositório:

### 2. Acesse o usuário root do Debian no terminal

> `su`

### 3. Acesse a pasta que você baixou o arquivo install.sh (Normalmente na pasta Downloads)

> `cd /home/user/Downloads/`

### 4. Transforme o arquivo install.sh em um arquivo execultavel

> `chmod +x install.sh`

### 5. Por fim execulte o arquivo

> `./install.sh`

---

## 🥽 Instruções de uso

- O arquivo CSV deve conter apenas uma coluna e a primeira linha deve ser o cabaçalho
- O extrato bancário deve estar da forma em que saiu do banco
- O sistema será iniciado localmente como > `http://localhost/`
- **Atenção:** caso algum arquivo for editado deve ser recolocado no FCSV para carregar os novos dados, senão o mesmo irá retornar erro ao comparar.

---
