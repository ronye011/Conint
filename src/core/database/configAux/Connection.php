<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    class Connection
    {
        public static function Connect()
        {
            try {
                $caminho_db = "/var/www/Conint/src/core/database/data.sqlite";
                $pdo = new PDO("sqlite:" . $caminho_db);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::createTables($pdo);

                return $pdo;
            } catch (PDOException $e) {
                die("Erro ao conectar ao banco de dados: " . $e->getMessage());
                return null;
            }
        }

        private static function createTables(PDO $db)
        {
            $senha = '$2y$10$Fn6kfMSxke1lRS2WZV/Xf.g8q4SzzeJ7ts4dW.rrEg8Q7OV/5TxL.';
            $sql = "
            CREATE TABLE IF NOT EXISTS usuarios (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nome TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                senha TEXT NOT NULL,
                token TEXT UNIQUE,
                status INT NOT NULL
            );

            INSERT INTO usuarios (nome, email, senha, status)
            SELECT 'ADM', 'adm@adm.com', '$senha', 0
            WHERE NOT EXISTS (
                SELECT 1 FROM usuarios WHERE id = 1
            );
            ";

            try {
                $db->exec($sql);
                return true;

            } catch (PDOException $e) {
                echo "Erro ao criar tabelas: " . $e->getMessage();
                return false;
            }
        }
    }
?>