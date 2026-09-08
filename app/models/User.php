<?php

class User
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function getByUsername(string $username)
    {
        $query = "SELECT id, nama, username, password, role FROM users WHERE username = :username LIMIT 1";

        $this->db->query($query);
        $this->db->bind('username', $username);

        return $this->db->single();
    }

    public function createUser(string $nama, string $username, string $passwordPlain, string $role = 'admin'): bool
    {
        $hash = password_hash($passwordPlain, PASSWORD_DEFAULT);

        $query = "INSERT INTO users (nama, username, password, role, created_at)
                  VALUES (:nama, :username, :password, :role, NOW())";

        $this->db->query($query);
        $this->db->bind('nama', $nama);
        $this->db->bind('username', $username);
        $this->db->bind('password', $hash);
        $this->db->bind('role', $role);

        return $this->db->execute();
    }
}
