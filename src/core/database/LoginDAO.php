<?php
    include_once( __DIR__ . "/configAux/Querys.php");

    class LoginDAO extends Querys {
        protected string $table = 'usuarios';
        protected int $id;
        protected array $columns = ['nome', 'email', 'senha', 'token', 'status'];

        public function login($email) {
            
            $sql = "SELECT * FROM {$this->table} WHERE email = ?";

            $pdo = Connection::Connect();

            $stmt = $pdo->prepare($sql);

            $stmt->execute([$email]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function saveToken($token, $id) {
            
            $this->id = (int) $id;

            $this->columns = [
                'token' => (string) $token
            ];

            return $this->update();
        }


        public function validateLogin() {

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $sql = "SELECT * FROM {$this->table} WHERE token = ?;";

            $pdo = Connection::Connect();

            $stmt = $pdo->prepare($sql);

            $stmt->execute([$_SESSION['token']]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }