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

    function validFileBankTXT($fileBank) {
        if (!isset($_FILES['fileBank']) || $_FILES['fileBank']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Erro no upload do arquivo.");
        }
    
        $arquivoTmp = $_FILES['fileBank']['tmp_name'];
        $nomeOriginal = $_FILES['fileBank']['name'];
        $extensaoArquivo = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
        $tipoArquivo = mime_content_type($arquivoTmp);
    
        $extensoesPermitidas = ['txt'];
        $tiposPermitidos = ['text/plain'];
    
        if (!in_array($extensaoArquivo, $extensoesPermitidas) || !in_array($tipoArquivo, $tiposPermitidos)) {
            throw new Exception("Formato inválido. Apenas arquivos .TXT são permitidos.");
        }
    
        // Retorna o path temporário do arquivo
        return $arquivoTmp;
    }    

    function processCsvDirect($fileCSV, $csvSeparate) {
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
    
        // Configurações de separadores
        $csvConfig = [
            "0" => ',',
            "1" => ';'
        ];
    
        if (!isset($csvConfig[$csvSeparate])) {
            throw new Exception("Separador inválido.");
        }
    
        $separator = $csvConfig[$csvSeparate];
    
        // Abre o arquivo temporário diretamente
        if (($handle = fopen($arquivoTmp, 'r')) !== false) {
            $cabecalho = fgetcsv($handle, 1000, $separator);
    
            if (count($cabecalho) !== 1) {
                throw new Exception("O arquivo CSV deve ter exatamente uma coluna.");
            }
    
            $dados = [];
    
            while (($linha = fgetcsv($handle, 1000, $separator)) !== false) {
                if (count($linha) !== count($cabecalho)) {
                    throw new Exception('O número de colunas na linha não corresponde ao cabeçalho.');
                }
    
                foreach ($linha as $celula) {
                    if (!is_numeric($celula) && $celula != null) {
                        throw new Exception("Todas as células devem ser numéricas, dado incorreto: " . $celula);
                    }
                }
    
                $dados[] = $linha[0];
            }
    
            fclose($handle);
            return $dados;
        } else {
            throw new Exception("Erro ao abrir o arquivo.");
        }
    }
?>
