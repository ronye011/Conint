<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    include_once( __DIR__ . "/Connection.php");

    abstract class Querys
    {
        protected PDO $pdo;
        protected string $table;
        protected int $id;
        protected array $columns = [];

        protected function selectByIDUser(): array {
            $query = "SELECT * FROM {$this->table} WHERE id = ? AND usuario_id = ?;";
            $this->pdo = Connection::Connect();
            $stmt = $this->pdo->prepare($query);
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $stmt->execute([$this->id, $_SESSION['id_user']]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        protected function selectByID(): array {
            $query = "SELECT * FROM {$this->table} WHERE id = ?;";
            $this->pdo = Connection::Connect();
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$this->id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        protected function delete(): bool {
            $query = "DELETE FROM {$this->table} WHERE id = ?";
            $this->pdo = Connection::Connect();
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute([$this->id]);
        }

        protected function insert() {
            $keys = array_keys($this->columns);
            $values = array_values($this->columns);
            $query = "INSERT INTO {$this->table} (" . implode(', ', $keys) . ") VALUES (" . implode(', ', array_fill(0, count($keys), '?')) . ");";
            $this->pdo = Connection::Connect();
            $stmt = $this->pdo->prepare($query);
            if ($stmt->execute($values)) {
                return (int) $this->pdo->lastInsertId(); // Retorna o ID inserido
            }
            return false;
        }

        protected function update(): bool {
            $keys = array_keys($this->columns);
            $values = array_values($this->columns);
            $setQuery = implode(', ', array_map(fn($k) => "$k = ?", $keys));
            $query = "UPDATE {$this->table} SET {$setQuery} WHERE id = ?;";
            $this->pdo = Connection::Connect();
            $stmt = $this->pdo->prepare($query);
            $values[] = $this->id;
            return $stmt->execute($values);
        }
    }

?>