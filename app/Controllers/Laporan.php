<?php

namespace App\Controllers;

use App\Models\ItemModel;
use App\Models\TransaksiModel;
use App\Models\PenjualanModel;
use Irsyadulibad\DataTables\DataTables;

class Laporan extends BaseController {

    protected $itemModel;
    protected $rules = ['harian' => ['rules' => 'required']];

    public function __construct()
    {
        $this->itemModel = new ItemModel();
        $this->transaksiModel = new TransaksiModel();
        $this->penjualanModel = new PenjualanModel();
		helper('form');

        // $db = \Config\Database::connect();
        // $builder = $db->table('tb_transaksi');
        // $query = $builder->get();
        // print_r($query);
    }

    public function ajax()
    {

        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        if ($this->request->isAJAX()) {
            return DataTables::use('tb_transaksi')
                ->select('DATE(tb_transaksi.created_at) as tanggal, tb_users.nama AS kasir, tb_item.nama_item as barang,  as stok_awal, jumlah_item as stok_keluar, tb_transaksi.harga_item as harga_item')
                ->join('tb_penjualan', 'tb_transaksi.id_penjualan = tb_penjualan.id', 'INNER JOIN')
                ->join('tb_users', 'tb_penjualan.id_user = tb_users.id', 'INNER JOIN')
                ->join('tb_item', 'tb_transaksi.id_item = tb_item.id', 'INNER JOIN')
                // ->join('tb_stok', 'tb_item.id = tb_stok.id_item', 'INNER JOIN')
                ->where(['DATE(tb_transaksi.created_at)' => date('Y-m-d')])
                ->make(true);
        }
    }

    public function harian()
    {
        $today = date("d F Y");

        echo view('laporan/harian', ['title' => 'Laporan Harian '.$today]);
    }

    public function download() {
        // Instansiasi Spreadsheet
        $spreadsheet = new Spreadsheet();
        // styling
        $style = [
            'font'      => ['bold' => true],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];
        $spreadsheet->getActiveSheet()->getStyle('A1:G1')->applyFromArray($style); // tambahkan style
        $spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(30); // setting tinggi baris
        // setting lebar kolom otomatis
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        // set kolom head
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', 'No')
            ->setCellValue('B1', 'Barcode')
            ->setCellValue('C1', 'Item Produk')
            ->setCellValue('D1', 'Kategori')
            ->setCellValue('E1', 'Unit')
            ->setCellValue('F1', 'Harga')
            ->setCellValue('G1', 'Stok');
        $row = 2;
        // looping data item
        foreach ($this->itemModel->detailItem() as $key => $data) {
            $spreadsheet->getActiveSheet()
                ->setCellValue('A' . $row, $key + 1)
                ->setCellValue('B' . $row, $data->barcode)
                ->setCellValue('C' . $row, $data->item)
                ->setCellValue('D' . $row, $data->kategori)
                ->setCellValue('E' . $row, $data->unit)
                ->setCellValue('F' . $row, $data->harga)
                ->setCellValue('G' . $row, $data->stok);
            $row++;
        }
        // tulis dalam format .xlsx
        $writer   = new Xlsx($spreadsheet);
        $namaFile = 'Daftar_Stok_Produk_' . date('d-m-Y');
        // Redirect hasil generate xlsx ke web browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename=' . $namaFile . '.xlsx');
        $writer->save('php://output');
        exit;
    }
}