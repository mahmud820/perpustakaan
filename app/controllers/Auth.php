<?php

class Auth extends Controller
{
    public function login()
    {
        if (AuthMiddleware::isLogin()) {
            header('Location: ' . BASEURL . '/admin');
            exit;
        }

        $data = [
            'judul' => 'Login',
            'error' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::guard();

            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            $usernameError = Validator::username($username);
            $passwordError = Validator::password($password);

            if ($usernameError !== null) {
                $data['error'] = $usernameError;
            } elseif ($passwordError !== null) {
                $data['error'] = $passwordError;
            } else {
                $sisaDetik = LoginThrottle::secondsUntilUnlocked($username);

                if ($sisaDetik > 0) {
                    $menit = (int) ceil($sisaDetik / 60);
                    $data['error'] = "Terlalu banyak percobaan gagal. Coba lagi dalam {$menit} menit.";
                } else {
                    $user = $this->model('User')->getByUsername($username);

                    if ($user && password_verify($password, $user['password'])) {
                        LoginThrottle::recordSuccess($username);
                        AuthMiddleware::markLoggedIn($user);

                        header('Location: ' . BASEURL . '/admin');
                        exit;
                    }

                    LoginThrottle::recordFailure($username);
                    $data['error'] = 'Username atau password salah.';
                }
            }
        }

        $this->view('auth/login', $data);
    }

    public function logout()
    {
        AuthMiddleware::destroySession();

        header('Location: ' . BASEURL . '/auth/login');
        exit;
    }
}
