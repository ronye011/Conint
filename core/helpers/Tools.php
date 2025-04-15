<?php
    function Comparator(array $dataBank, array $dataCsv, $compare) {
        $compare = (int) $compare;
        $notFound = [];
    
        $normalize = function($value) {
            if (is_null($value) || trim($value) === '') {
                return -1; // Ou retorne um valor que represente 'inválido', como -1
            }
            return (int) trim($value);
        };
    
        if ($compare == 1) {
            foreach ($dataBank as $a) {
                $same = false;
                foreach ($dataCsv as $b) {
                    if ($normalize($a) === $normalize($b)) {
                        $same = true;
                        break;
                    }
                }
                if (!$same) {
                    $notFound[] = $a;
                }
            }
        } else {
            foreach ($dataCsv as $a) {
                $same = false;
                foreach ($dataBank as $b) {
                    if ($normalize($a) === $normalize($b)) {
                        $same = true;
                        break;
                    }
                }
                if (!$same) {
                    $notFound[] = $a;
                }
            }
        }
    
        return $notFound;
    }

    function validFileCSV($fileCSV) {
        // Verifica se o arquivo foi enviado corretamente
        if (!isset($_FILES['fileCSV']) || $_FILES['fileCSV']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Erro no upload do arquivo.");
        }
    
        // Dados do arquivo
        $arquivoTmp = $_FILES['fileCSV']['tmp_name'];
        $nomeOriginal = $_FILES['fileCSV']['name'];
        $extensaoArquivo = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
        $tipoArquivo = mime_content_type($arquivoTmp);
    
        // Extensões e tipos MIME permitidos
        $extensoesPermitidas = ['csv'];
        $tiposPermitidos = [
            'text/csv',
            'application/csv',
            'text/plain',
            'application/vnd.ms-excel',
            'application/octet-stream'
        ];
    
        // Validação do arquivo
        if (!in_array($extensaoArquivo, $extensoesPermitidas) || !in_array($tipoArquivo, $tiposPermitidos)) {
            throw new Exception("Formato inválido. Apenas arquivos .CSV são permitidos.");
        }
    
        // Gera um nome único
        $nomeUnico = str_replace('.', '', uniqid("csv_", true)) . "_" . time() . "." . $extensaoArquivo;
    
        // Caminho de destino
        $caminhoPasta = realpath(__DIR__ . "/../../filesTemp/") . "/";
        
        // Verifica se o diretório existe
        if (!is_dir($caminhoPasta)) {
            throw new Exception("Diretório de destino não encontrado.");
        }
    
        $caminhoDestino = $caminhoPasta . $nomeUnico;
    
        // Move o arquivo
        if (!move_uploaded_file($arquivoTmp, $caminhoDestino)) {
            throw new Exception("Erro ao mover o arquivo para o diretório de destino.");
        }
    
        // Retorna o nome do arquivo salvo
        return $nomeUnico;
    }

    function validFileBankTXT($fileBank) {
        // Verifica se o arquivo foi enviado corretamente
        if (!isset($_FILES['fileBank']) || $_FILES['fileBank']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Erro no upload do arquivo.");
        }

        // Dados do arquivo
        $arquivoTmp = $_FILES['fileBank']['tmp_name'];
        $nomeOriginal = $_FILES['fileBank']['name'];
        $extensaoArquivo = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
        $tipoArquivo = mime_content_type($arquivoTmp);

        // Extensões e tipos MIME permitidos
        $extensoesPermitidas = ['txt'];
        $tiposPermitidos = ['text/plain'];

        // Validação do arquivo
        if (!in_array($extensaoArquivo, $extensoesPermitidas) || !in_array($tipoArquivo, $tiposPermitidos)) {
            throw new Exception("Formato inválido. Apenas arquivos .TXT são permitidos.");
        }

        // Gera um nome único
        $nomeUnico = str_replace('.', '', uniqid("bank_", true)) . "_" . time() . "." . $extensaoArquivo;

        // Caminho de destino
        $caminhoPasta = realpath(__DIR__ . "/../../filesTemp/") . "/";
        $caminhoDestino = $caminhoPasta . $nomeUnico;

        // Move o arquivo
        if (!move_uploaded_file($arquivoTmp, $caminhoDestino)) {
            throw new Exception("Erro ao mover o arquivo para o diretório de destino.");
        }

        // Retorna o nome do arquivo salvo
        return $nomeUnico;
    }

    function dataCsvExtract($nameCSV, $csvSeparate) {
        // Caminho para o arquivo CSV
        $caminhoArquivo = realpath(__DIR__ . "/../../filesTemp/") . "/";
        $caminhoArquivo = $caminhoArquivo . $nameCSV;
    
        // Verifica se o arquivo existe
        if (!file_exists($caminhoArquivo) || !is_readable($caminhoArquivo)) {
            die('Arquivo não encontrado ou não é legível.');
        }
    
        $csvConfig = [
            "0" => ',',
            "1" => ';'
        ];
        
        // Verifica se o separador é válido
        if (!isset($csvConfig[$csvSeparate])) {
            throw new Exception("Separador inválido.");
        }
        $separator = $csvConfig[$csvSeparate];
        
        // Abre o arquivo para leitura
        if (($handle = fopen($caminhoArquivo, 'r')) !== false) {
            // Lê o cabeçalho
            $cabecalho = fgetcsv($handle, 1000, $separator);
        
            // Valida se o cabeçalho tem exatamente uma coluna
            if (count($cabecalho) !== 1) {
                throw new Exception("O arquivo CSV deve ter exatamente uma coluna.");
            }
        
            $dados = []; // Inicializa o array de dados
        
            // Lê as linhas do arquivo
            while (($linha = fgetcsv($handle, 1000, $separator)) !== false) {
                // Verifica se o número de colunas na linha corresponde ao cabeçalho
                if (count($linha) !== count($cabecalho)) {
                    throw new Exception('O número de colunas na linha não corresponde ao cabeçalho.');
                }
    
                // Valida se todas as células são numéricas ou em branco
                foreach ($linha as $celula) {
                    if (!is_numeric($celula) && $celula != null) {
                        throw new Exception("Todas as células devem ser numéricas, dado incorreto: " . $celula);
                    }
                }
    
                // Adiciona só o valor da célula (já que só tem uma)
                $dados[] = $linha[0];
            }
    
            // Fecha o arquivo
            fclose($handle);
    
            // Retorna apenas os valores simples
            return $dados;
        } else {
            throw new Exception("Erro ao abrir o arquivo.");
        }
    }
    
    function creanTempFiles() {
        $caminhoPasta = realpath(__DIR__ . "/../../filesTemp/") . "/";
        if (!is_dir($caminhoPasta)) {
            throw new Exception("Diretório não encontrado: filesTemp");
        }
    
        $arquivos = glob($caminhoPasta . '/*'); // Pega todos os arquivos da pasta
    
        foreach ($arquivos as $arquivo) {
            if (is_file($arquivo)) {
                unlink($arquivo); // Apaga o arquivo
            }
        }
    }

    function creanResultFiles() {
        $caminhoPasta = realpath(__DIR__ . "/../../result/") . "/";
        if (!is_dir($caminhoPasta)) {
            throw new Exception("Diretório não encontrado: result");
        }
    
        $arquivos = glob($caminhoPasta . '/*'); // Pega todos os arquivos da pasta
    
        foreach ($arquivos as $arquivo) {
            if (is_file($arquivo)) {
                unlink($arquivo); // Apaga o arquivo
            }
        }
    }

    function createResultCsvFile(array $data) {
        $nomeUnico = str_replace('.', '', uniqid("resultCSV_", true)) . "_" . time() . ".csv";
        $caminhoPasta = realpath(__DIR__ . "/../../result/") . "/";
        $caminhoDestino = $caminhoPasta . $nomeUnico;
    
        $file = fopen($caminhoDestino, "w");
    
        if ($file === false) {
            throw new Exception("Erro ao abrir o arquivo para escrita.");
        }
    
        foreach ($data as $row) {
            fputcsv($file, is_array($row) ? $row : [$row]);
        }
    
        fclose($file);
        return $nomeUnico;
    }
?>