<?php
    include_once('../database/LoginDAO.php');

    class Login {
        private string $email;
        private string $password;

        public function __construct(array $data) {

            $data['email'] = $data['email'] ?? '';

            if(filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $this->email = $data['email'];
            } else {
                return ("Email inválido.");
            }

            $data['password'] = $data['senha'] ?? '';
            $this->password = hash_hmac("sha256", $data['password'], "localhost");
        }

        public function login() {

            $loginDao = new LoginDAO();
            $user = $loginDao->login($this->email);

            if($user && password_verify($this->password, $user[0]['senha']) && $user[0]['status'] === 0) {
                
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                session_regenerate_id(true);

                $token = bin2hex(random_bytes(32));
                $loginDao->saveToken($token, $user[0]['id']);
                $_SESSION['id'] = $user[0]['id'];
                $_SESSION['token'] = $token;

                return true;
            } else {
                return false;
            }
        }

        public static function validLogin(): bool {

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            if(!isset($_SESSION['id']) || !isset($_SESSION['token'])) {
                return false;
            }

            $loginDAO = new LoginDAO();
        
            if(count($loginDAO->validateLogin()) == 1) {
                return true;
            }
            return false;
        }
    }