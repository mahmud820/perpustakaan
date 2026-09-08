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
            // =========================
            // CSRF PROTECTION
            // =========================
            Csrf::guard();

            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            // =========================
            // INPUT VALIDATION
            // =========================
            if ($username === '' || $password === '') {
                $data['error'] = 'Username dan password wajib diisi.';
            } elseif (mb_strlen($username) > 50) {
                $data['error'] = 'Username tidak valid.';
            } else {
                // =========================
                // RATE LIMITING (anti brute force)
                // =========================
                $sisaDetik = LoginThrottle::secondsUntilUnlocked($username);

                if ($sisaDetik > 0) {
                    $menit = (int) ceil($sisaDetik / 60);
                    $data['error'] = "Terlalu banyak percobaan gagal. Coba lagi dalam {$menit} menit.";
                } else {
                    $user = $this->model('User')->getByUsername($username);

                    // =========================
                    // PASSWORD VERIFICATION
                    // =========================
                    if ($user && password_verify($password, $user['password'])) {
                        LoginThrottle::recordSuccess($username);

                        AuthMiddleware::markLoggedIn($user);

                        header('Location: ' . BASEURL . '/admin');
                        exit;
                    }

                    LoginThrottle::recordFailure($username);

                    // Pesan disamakan (tidak membedakan "user tidak ada" vs "password salah")
                    // supaya tidak bisa dipakai enumerasi username yang valid.
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
