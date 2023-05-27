<?php

namespace App\Controllers;

use App\Models\ItemModel;
use App\Models\TransaksiModel;
use App\Models\PenjualanModel;
use Irsyadulibad\DataTables\DataTables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Laporan extends BaseController {

    protected $itemModel;
    protected $rules = ['harian' => ['rules' => 'required']];
    private $monthNames = [
        '01' => 'January',
        '02' => 'February',
        '03' => 'March',
        '04' => 'April',
        '05' => 'May',
        '06' => 'June',
        '07' => 'July',
        '08' => 'August',
        '09' => 'September',
        '10' => 'October',
        '11' => 'November',
        '12' => 'Desember',
    ];

    public function __construct()
    {
        $this->penjualanModel = new PenjualanModel();
		helper('form');

        // $db = \Config\Database::connect();
        // $builder = $db->table('tb_transaksi');
        // $query = $builder->get();
        // print_r($query);
    }

    public function harian()
    {   
        $searchDate = $this->request->getVar('searchDate');

        if($searchDate == null) {
            $searchDate = date("Y-m-d");
        }
        
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        if ($this->request->isAJAX()) {

            return DataTables::use('tb_penjualan')
                ->select('tb_penjualan.invoice AS invoice, 
                tb_users.nama AS nama_kasir,
                tb_pelanggan.nama_pelanggan AS nama_pelanggan, 
                tb_penjualan.total_akhir AS total_penjualan, 
                tb_penjualan.tunai AS pembayaran, 
                tb_penjualan.kembalian AS saldo_akhir')
                ->join('tb_pelanggan', 'tb_pelanggan.id = tb_penjualan.id_pelanggan', 'INNER JOIN')
                ->join('tb_users', 'tb_users.id = tb_penjualan.id_user', 'INNER JOIN')
                ->where(['DATE(tb_penjualan.created_at)' => $searchDate])
                ->make(true);
        }
        $date = ($searchDate) ? date("d F Y", strtotime($searchDate)) : date("d F Y");

        echo view('laporan/harian', ['title' => 'Laporan Harian '.$date]);
    }

    public function mingguan()
    {
        $searchWeek = $this->request->getVar('searchWeek');

        if($searchWeek == null) {
            $searchWeek = date("YW");
        }

        $week = ($searchWeek) ? substr($searchWeek, 4) : date("W");
        $year = ($searchWeek) ? substr($searchWeek, 0, 4) : date("Y");

        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        if ($this->request->isAJAX()) {

            return DataTables::use('tb_penjualan')
                ->select('tb_penjualan.invoice AS invoice, 
                tb_users.nama AS nama_kasir,
                tb_pelanggan.nama_pelanggan AS nama_pelanggan, 
                tb_penjualan.total_akhir AS total_penjualan, 
                tb_penjualan.tunai AS pembayaran, 
                tb_penjualan.kembalian AS saldo_akhir')
                ->join('tb_pelanggan', 'tb_pelanggan.id = tb_penjualan.id_pelanggan', 'INNER JOIN')
                ->join('tb_users', 'tb_users.id = tb_penjualan.id_user', 'INNER JOIN')
                ->where(['YEARWEEK(tb_penjualan.created_at)' => $searchWeek])
                ->make(true);
        }

        echo view('laporan/mingguan', ['title' => 'Laporan Minggu '.$week.' Tahun '.$year]);
    }
    
    public function bulanan()
    {
        $searchMonth = $this->request->getVar('searchMonth');

        if($searchMonth == null) {
            $searchMonth = date("Ym");
        }
        
        $year = ($searchMonth) ? substr($searchMonth, 0, 4) : date("Y");
        $month = ($searchMonth) ? substr($searchMonth, 4) : date("m");
        $month = trim($month, '-');
        
        $formattedMonth = $this->monthNames[$month];   

        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        if ($this->request->isAJAX()) {
            return DataTables::use('tb_penjualan')
                ->select('tb_penjualan.invoice AS invoice, 
                tb_users.nama AS nama_kasir,
                tb_pelanggan.nama_pelanggan AS nama_pelanggan, 
                tb_penjualan.total_akhir AS total_penjualan, 
                tb_penjualan.tunai AS pembayaran, 
                tb_penjualan.kembalian AS saldo_akhir')
                ->join('tb_pelanggan', 'tb_pelanggan.id = tb_penjualan.id_pelanggan', 'INNER JOIN')
                ->join('tb_users', 'tb_users.id = tb_penjualan.id_user', 'INNER JOIN')
                ->where(['MONTH(tb_penjualan.created_at)' => $month])
                ->make(true);
        }

        echo view('laporan/bulanan', ['title' => 'Laporan Bulan '.$formattedMonth.' '. $year]);
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
            ->setCellValue('B1', 'Invoice')
            ->setCellValue('C1', 'Kasir')
            ->setCellValue('D1', 'Pelanggan')
            ->setCellValue('E1', 'Total Penjualan')
            ->setCellValue('F1', 'Pembayaran')
            ->setCellValue('G1', 'Saldo Akhir');
        $row = 2;
        // looping data item
        $searchDate = $this->request->getVar('searchDate');
        $searchWeek = $this->request->getVar('searchWeek');
        $searchMonth = $this->request->getVar('searchMonth');

        $date = null;
        $namaFile = null;
        $laporan = null;

        if ($searchDate !== null) {
            $date = ($searchDate) ? date("d F Y", strtotime($searchDate)) : date("d F Y");
            $namaFile = 'Laporan_Penjualan_' . str_replace(' ', '-', $date);
            $laporan = $this->penjualanModel->detailLaporan(null, $date, null, null);
        } else if ($searchWeek !== null) {
            $week = ($searchWeek) ? substr($searchWeek, 4) : date("W");
            $year = ($searchWeek) ? substr($searchWeek, 0, 4) : date("Y");
            $date = $year . $week;
            $namaFile = 'Laporan_Penjualan_Minggu-' . $week . '_' . $year;
            $laporan = $this->penjualanModel->detailLaporan(null, null, $date, null);
        } else if ($searchMonth !== null) {
            $year = ($searchMonth) ? substr($searchMonth, 0, 4) : date("Y");
            $month = ($searchMonth) ? substr($searchMonth, 5) : date("m");
            $date = $year . $month;
            
            $month = trim($month, '-');
            $formattedMonth = $this->monthNames[$month];

            $namaFile = 'Laporan_Penjualan_Bulan_' . $formattedMonth . '-' . $year;
            $laporan = $this->penjualanModel->detailLaporan(null, null, null , $month);
        }

        foreach ($laporan as $key => $data) :
            $spreadsheet->getActiveSheet()
                ->setCellValue('A' . $row, $key+1)
                ->setCellValue('B' . $row, $data->invoice)
                ->setCellValue('C' . $row, $data->nama_kasir)
                ->setCellValue('D' . $row, $data->nama_pelanggan)
                ->setCellValue('E' . $row, $data->total_penjualan)
                ->setCellValue('F' . $row, $data->pembayaran)
                ->setCellValue('G' . $row, $data->saldo_akhir);
            $row++;
        endforeach;
        // tulis dalam format .xlsx
        $writer   = new Xlsx($spreadsheet);
        // $namaFile = 'Laporan_Tanggal_' . str_replace(' ', '_', $date);
        // Redirect hasil generate xlsx ke web browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename=' . $namaFile . '.xlsx');
        $writer->save('php://output');
        exit;
    }
}