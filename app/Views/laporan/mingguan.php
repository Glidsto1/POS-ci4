<?php $this->extend('layout/template'); ?>
<?php $this->section('content'); ?>

<div class="container-fluid">
    <div class="form-inline mb-2 d-flex justify-content-between">
        <button class="btn btn-success mb-1 export-excel"><i class="fas fa-file-excel"></i> Export</button>
        <div class="d-flex align-items-center">
            <label for="search-week" class="mr-2">Search by:</label>
            <input type="week" id="search-week" class="form-control mr-2">
            <button id="search-button" class="btn btn-primary">Search</button>
        </div>
    </div>
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <?= csrf_field('token'); ?>
                <table class="table table-bordered table-striped" id="table-laporan-mingguan" width="100%">
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
    const table = $("#table-laporan-mingguan").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: `${BASE_URL}/laporan/mingguan`,
            data: function(params) {
                var searchWeek = $("#search-week").val();
                var year = searchWeek.substring(0, 4);
                var week = searchWeek.substring(6);
                params.searchWeek = year + week;
                // params.searchWeek = $("#search-week").val();
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
        var searchWeek = $("#search-week").val();
        var year = searchWeek.substring(0, 4);
        var week = searchWeek.substring(6);
        searchWeek = year + week;

        table.ajax.url(`${BASE_URL}/laporan/mingguan?searchWeek=${searchWeek}`).load();
        console.log(searchWeek);

        var title = "Laporan Minggu ";

        if (searchWeek !== "") {
            title += week + " Tahun " + year;
        } else {
            var today = new Date();
            var year = today.getFullYear();
            var week = getISOWeek(today);

            title += week + " Tahun " + year;
        }

        // Update the title in the view
        $(".header-title").text(title);
    });

    $(".export-excel").on("click", function() {
        var searchWeek = $("#search-week").val();
        console.log(searchWeek);
        location.href = `${BASE_URL}/laporan/download?searchWeek=${searchWeek}`;
    })

    function getISOWeek(date) {
        var target = new Date(date.valueOf());
        var dayNumber = (date.getDay() + 6) % 7;

        target.setDate(target.getDate() - dayNumber + 3);

        var firstThursday = target.valueOf();
        target.setMonth(0, 1);

        if (target.getDay() !== 4) {
            target.setMonth(0, 1 + ((4 - target.getDay()) + 7) % 7);
        }

        return 1 + Math.ceil((firstThursday - target) / 604800000);
    }

});
</script>
<?php $this->endSection(); ?>