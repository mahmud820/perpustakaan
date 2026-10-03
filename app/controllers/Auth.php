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

            // post() aman terhadap input array (username[]=x); tanpa ini trim() melempar TypeError -> 500
            $username = $this->post('username');

            // Password TIDAK boleh di-trim (spasi bisa bagian dari password), tapi harus berupa string
            $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

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
        // Logout mengubah state (mengakhiri sesi), jadi tidak boleh bisa dipicu lewat link/gambar biasa (GET):
        // situs lain bisa menyisipkan <img src=".../auth/logout"> untuk memaksa admin keluar.
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            echo '405 Method Not Allowed';
            return;
        }

        // Sesi sudah habis / belum login: tidak ada yang perlu dilindungi, langsung ke halaman login
        if (!AuthMiddleware::isLogin()) {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        Csrf::guard();

        AuthMiddleware::destroySession();

        header('Location: ' . BASEURL . '/auth/login');
        exit;
    }
}
