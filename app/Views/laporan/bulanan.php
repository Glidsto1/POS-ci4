<?php $this->extend('layout/template'); ?>
<?php $this->section('content'); ?>

<div class="container-fluid">
    <div class="form-inline mb-2 d-flex justify-content-between">
        <button class="btn btn-success mb-1 export-excel"><i class="fas fa-file-excel"></i> Export</button>
        <div class="d-flex align-items-center">
            <label for="search-month" class="mr-2">Search by date:</label>
            <input type="month" id="search-month" class="form-control mr-2">
            <button id="search-button" class="btn btn-primary">Search</button>
        </div>
    </div>
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <?= csrf_field('token'); ?>
                <table class="table table-bordered table-striped" id="table-laporan-bulanan" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Invoice</th>
                            <th>Kasir</th>
                            <th>Pelanggan</th>
                            <th>Total Penjualan</th>
                            <th>Pembayaran</th>
                            <th>Saldo Akhir</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('js'); ?>
<script>
$(document).ready(function() {
    const table = $("#table-laporan-bulanan").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: `${BASE_URL}/laporan/bulanan`,
            data: function(params) {
                params.searchMonth = $("#search-month").val();
            }
        },
        lengthMenu: [
            [5, 10, 50, 100],
            [5, 10, 50, 100]
        ], //Combobox Limit
        columns: [{
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {
                data: 'invoice',
                name: 'invoice',
            },
            {
                data: 'nama_kasir',
                name: 'nama_kasir',
            },
            {
                data: 'nama_pelanggan',
                name: 'nama_pelanggan'
            },
            {
                data: 'total_penjualan',
                name: 'total_penjualan',
            },
            {
                data: 'pembayaran',
                name: 'pembayaran',
            },
            {
                data: 'saldo_akhir',
                name: 'saldo_akhir',
            },
        ],
        "columnDefs": [{
            targets: 0,
            width: "5%",
        }, {
            targets: [2, 3, 4, 5],
            orderable: false
        }]
    })

    
    $("#search-button").on("click", function() {
        var searchMonth = $("#search-month").val();
        var year = searchMonth.substring(0, 4);
        var month = searchMonth.substring(5);
        searchMonth = year + month;
        table.ajax.url(`${BASE_URL}/laporan/bulanan?searchMonth=${searchMonth}`).load();

        console.log(searchMonth);

        var title = "Laporan Bulan";

        if (searchMonth !== "") {
            const date = new Date();
            date.setMonth(month-1);

            month = date.toLocaleString('default', {
                month: 'long'
            });

            title += " " + month + " " + year;
        } else {
            var today = new Date();
            var currentMonth = today.toLocaleString('default', {
                month: 'long'
            });
            var currentYear = today.getFullYear();

            title += " " + currentMonth + " " + currentYear;
        }

        // Update the title in the view
        $(".header-title").text(title);
    });
    $(".export-excel").on("click", function() {
        var searchMonth = $("#search-month").val();
        console.log(searchMonth);
        location.href = `${BASE_URL}/laporan/download?searchMonth=${searchMonth}`;
    })
});
</script>
<?php $this->endSection(); ?>