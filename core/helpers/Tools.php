<?php
    function Comparator(array $dataCsv, array $dataBank, $compare) {
        $compare = (int) $compare;
        $notFound = [];
    
        $normalize = function($value) {
            if (is_null($value) || trim((string) $value) === '') {
                return -1;
            }
            return trim((string) $value);
        };
    
        $getDataField = function($entry) {
            return is_array($entry) ? ($entry['data'] ?? '') : $entry;
        };
    
        if ($compare == 1) {
            foreach ($dataBank as $a) {
                $same = false;
                foreach ($dataCsv as $b) {
                    if ($normalize($getDataField($a)) === $normalize($getDataField($b))) {
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
                    if ($normalize($getDataField($a)) === $normalize($getDataField($b))) {
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

    function validFileTXT($fileBank) {
        if (!isset($fileBank['error']) || $fileBank['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Erro no upload do arquivo.");
        }
    
        $arquivoTmp = $fileBank['tmp_name'];
        $nomeOriginal = $fileBank['name'];
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

    function validFileCSV($csvFile) {
        // Verifica se o array é válido
        if (!isset($csvFile['error']) || $csvFile['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Erro no upload do arquivo.");
        }
    
        $arquivoTmp = $csvFile['tmp_name'];
        $nomeOriginal = $csvFile['name'];
        $extensaoArquivo = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
        $tipoArquivo = mime_content_type($arquivoTmp);
    
        $extensoesPermitidas = ['csv'];
        $tiposPermitidos = [
            'text/csv',
            'application/csv',
            'text/plain',
            'application/vnd.ms-excel',
            'application/octet-stream'
        ];
    
        if (!in_array($extensaoArquivo, $extensoesPermitidas) || !in_array($tipoArquivo, $tiposPermitidos)) {
            throw new Exception("Formato inválido. Apenas arquivos .CSV são permitidos.");
        }

        return $csvFile;
    }

    function validFileXLSX($xlsxFile) {
        // Verifica se o array é válido
        if (!isset($xlsxFile['error']) || $xlsxFile['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Erro no upload do arquivo.");
        }
    
        $arquivoTmp = $xlsxFile['tmp_name'];
        $nomeOriginal = $xlsxFile['name'];
        $extensaoArquivo = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
        $tipoArquivo = mime_content_type($arquivoTmp);
    
        $extensoesPermitidas = ['xlsx'];
        $tiposPermitidos = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/octet-stream',
            'application/zip', // às vezes o xlsx é detectado como zip
            'application/vnd.ms-excel'
        ];
    
        if (!in_array($extensaoArquivo, $extensoesPermitidas) || !in_array($tipoArquivo, $tiposPermitidos)) {
            throw new Exception("Formato inválido. Apenas arquivos .XLSX são permitidos.");
        }
    
        return $xlsxFile;
    }
    
    function validFileXLS($xlsFile) {
        // Verifica se o array é válido
        if (!isset($xlsFile['error']) || $xlsFile['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Erro no upload do arquivo.");
        }
    
        $arquivoTmp = $xlsFile['tmp_name'];
        $nomeOriginal = $xlsFile['name'];
        $extensaoArquivo = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
        $tipoArquivo = mime_content_type($arquivoTmp);
    
        $extensoesPermitidas = ['xls'];
        $tiposPermitidos = [
            'application/vnd.ms-excel',
            'application/octet-stream',
            'application/x-msexcel'
        ];
    
        if (!in_array($extensaoArquivo, $extensoesPermitidas) || !in_array($tipoArquivo, $tiposPermitidos)) {
            throw new Exception("Formato inválido. Apenas arquivos .XLS são permitidos.");
        }
    
        return $xlsFile;
    }
?>
