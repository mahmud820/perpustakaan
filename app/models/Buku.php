<?php

class Buku
{
    private $db;
    private $folderCover = '../public/img/dataGambar/';

    public function __construct()
    {
        $this->db = new Database;
    }

    // Ambil semua buku
    public function getAllBuku()
    {
        $query = "SELECT * FROM buku ORDER BY created_at DESC";

        $this->db->query($query);

        return $this->db->resultSet();
    }

    // Ambil buku berdasarkan id
    public function getBukuById($id)
    {
        $query = "SELECT * FROM buku WHERE id = :id";

        $this->db->query($query);
        $this->db->bind('id', $id);

        return $this->db->single();
    }

    // Tambah buku
    public function tambahBuku($data, $files)
    {

        $cover = '';

        if ($files['cover']['name'] != '') {

            $cover = time() . '_' . $files['cover']['name'];

            move_uploaded_file(
                $files['cover']['tmp_name'],
                $this->folderCover . $cover
            );
        }

        $query = "INSERT INTO buku
                (
                    judul,
                    penulis,
                    klasifikasi,
                    sinopsis,
                    cover,
                    link_baca
                )
                VALUES
                (
                    :judul,
                    :penulis,
                    :klasifikasi,
                    :sinopsis,
                    :cover,
                    :link_baca
                )";

        $this->db->query($query);

        $this->db->bind('judul', $data['judul']);
        $this->db->bind('penulis', $data['penulis']);
        $this->db->bind('klasifikasi', $data['klasifikasi']);
        $this->db->bind('sinopsis', $data['sinopsis']);
        $this->db->bind('cover', $cover);
        $this->db->bind('link_baca', $data['link_baca']);

        $this->db->execute();

        return $this->db->rowCount();
    }

    // Update buku
    public function updateBuku($data, $files)
    {
        $cover = $data['cover_lama'];

        if ($files['cover']['name'] != '') {

            if (
                $data['cover_lama'] != '' &&
                file_exists($this->folderCover . $data['cover_lama'])
            ) {
                unlink($this->folderCover . $data['cover_lama']);
            }

            $cover = time() . '_' . $files['cover']['name'];

            move_uploaded_file(
                $files['cover']['tmp_name'],
                $this->folderCover . $cover
            );
        }

        $query = "UPDATE buku SET
                    judul = :judul,
                    penulis = :penulis,
                    klasifikasi = :klasifikasi,
                    sinopsis = :sinopsis,
                    cover = :cover,
                    link_baca = :link_baca
                WHERE id = :id";

        $this->db->query($query);

        $this->db->bind('id', $data['id']);
        $this->db->bind('judul', $data['judul']);
        $this->db->bind('penulis', $data['penulis']);
        $this->db->bind('klasifikasi', $data['klasifikasi']);
        $this->db->bind('sinopsis', $data['sinopsis']);
        $this->db->bind('cover', $cover);
        $this->db->bind('link_baca', $data['link_baca']);

        $this->db->execute();

        return $this->db->rowCount();
    }

    // Hapus buku
    public function hapusBuku($data)
    {

        if (
            !empty($data['cover']) &&
            file_exists($this->folderCover . $data['cover'])
        ) {

            unlink($this->folderCover . $data['cover']);
        }

        $query = "DELETE FROM buku WHERE id = :id";

        $this->db->query($query);
        $this->db->bind('id', $data['id']);

        $this->db->execute();

        return $this->db->rowCount();
    }

    // Fitur Pencarian
    public function cariBuku($keyword)
    {
        $query = "SELECT * FROM buku
              WHERE judul LIKE :keyword
              OR penulis LIKE :keyword
              OR klasifikasi LIKE :keyword
              ORDER BY created_at DESC";

        $this->db->query($query);

        $this->db->bind(
            'keyword',
            '%' . $keyword . '%'
        );

        return $this->db->resultSet();
    }
}
