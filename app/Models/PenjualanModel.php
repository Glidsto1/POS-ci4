<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\TransaksiModel;
use App\Models\ItemModel;

class PenjualanModel extends Model
{
    protected $table      = 'tb_penjualan';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'invoice',
        'id_pelanggan',
        'total_harga',
        'diskon',
        'total_akhir',
        'tunai',
        'kembalian',
        'catatan',
        'tanggal',
        'id_user',
        'ip_address'
    ];
    protected $useTimestamps = true;

    public function invoice()
    {
        // ambil invoice terakhir sesuai tanggal hari ini
        $builder = $this->builder($this->table)->selectMax('invoice')->where('tanggal', date('Y-m-d'))->get(1)->getRow();
        // buat format invoice baru
        if (empty($builder->invoice)) {
            $no = '0001';
        } else {
            $data = substr($builder->invoice, -4); // ambil 4 angka ke belakang
            $angka = ((int) $data) + 1;
            $no = sprintf("%'.04d", $angka);
        }
        return "INV" . date('ymd') . $no;
    }

    public function simpanPenjualan(array $post)
    {
        $item = new ItemModel();
        $transaksi = new TransaksiModel();

        $db = \Config\Database::connect();
        $db->transBegin();
        $this->save($post); // simpan transaksi ke tabel penjualan
        $id_penjualan = $this->insertID; // mengambil id penjualan
        $keranjang = session('keranjang'); // menampung session keranjang
        $data = [];
        foreach ($keranjang as $val) {
            $itemTransaksi = [
                'id_penjualan'  => $id_penjualan,
                'id_item'       => $val['id'],
                'harga_item'    => $val['harga'],
                'jumlah_item'   => $val['jumlah'],
                'diskon_item'   => $val['diskon'],
                'subtotal'      => $val['total'],
                'ip_address'    => $post['ip_address'],
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s"),
            ];
            array_push($data, $itemTransaksi); // masukan item transaksi ke variabel $data
            // update stok item sesuai idnya
            $item->set('stok', 'stok-'.$val['jumlah'], false);
            $item->where('id', $val['id']);
            $item->update();
        }
        $transaksi->insertBatch($data); // tambahkan ke tabel transaksi

        if ($db->transStatus() === FALSE)
        {
            $db->transRollback();
            return ['status' => false];
        } else {
            // kosongkang keranjang
            unset($_SESSION['keranjang']);
            return ['status' => $db->transCommit(), 'id' => $id_penjualan];
        }
    }


    public function laporanPenjualan($tahun)
    {
        return $this->builder('tb_bulan_tahun')->select('bulan')->selectCount('invoice', 'total')->join('tb_penjualan', 'date_format(created_at, "%m-%Y") = bln_thn', 'left')->where('tahun', $tahun)->groupBy('bln_thn')->get()->getResult();
    }

    public function detailLaporan($id = null, $harian = null, $mingguan = null, $bulanan = null)
    {
        $builder = $this->builder($this->table)
            ->select('tb_penjualan.invoice AS invoice, 
                tb_users.nama AS nama_kasir,
                tb_pelanggan.nama_pelanggan AS nama_pelanggan, 
                tb_penjualan.total_akhir AS total_penjualan, 
                tb_penjualan.tunai AS pembayaran, 
                tb_penjualan.kembalian AS saldo_akhir')
            ->join('tb_pelanggan', 'tb_pelanggan.id = tb_penjualan.id_pelanggan')
            ->join('tb_users', 'tb_users.id = tb_penjualan.id_user');

        if ($harian != null) {
            $date = ($harian) ? date("Y-m-d", strtotime($harian)) : date("Y-m-d");
            $builder->where('DATE(tb_penjualan.created_at)', $date);
        } else if ($mingguan != null) {
            $builder->where('YEARWEEK(tb_penjualan.created_at)', $mingguan);
        } else if ($bulanan != null) {
            $builder->where('MONTH(tb_penjualan.created_at)', $bulanan);
        }

        if (empty($id)) {
            return $builder->get()->getResult(); // tampilkan semua data
        }
    }
}