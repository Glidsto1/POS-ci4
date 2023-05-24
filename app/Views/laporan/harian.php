<?php $this->extend('layout/template'); ?>
<?php $this->section('content'); ?>

<div class="container-fluid">
    <button class="btn btn-success mb-1 export-excel"><i class="fas fa-file-excel"></i> Export</button>
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <?= csrf_field('token'); ?>
                <table class="table table-bordered table-striped" id="table-laporan-harian" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tanggal Transaksi</th>
                            <th>Kasir</th>
                            <th>Nama Barang</th>
                            <th>Stok Awal</th>
                            <th>Stok Keluar</th>
                            <th>Sisa Stok</th>
                            <th>Harga Terjual</th>
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
    const table = $("#table-laporan-harian").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: `${BASE_URL}/laporan/ajax`
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
            // {   
            //     data: 'id_transaksi',
            //     name: 'id_transaksi',
            // },
            {
                data: 'tanggal',
                name: 'tanggal',
            },
            {
                data: 'kasir',
                name: 'kasir'
            },
            {
                data: 'barang',
                name: 'barang',
            },
            {
                data: null,
                    render: function(data, type, row) {
                        var totalStok = parseFloat(data.stok_awal) + parseFloat(data
                            .stok_keluar);
                        return totalStok;
                    },
                name: 'total_stok'
            },
            {
                data: 'stok_keluar',
                name: 'stok_keluar'
            },
            {
                data: 'stok_awal',
                name: 'stok_awal'
            },
            {
                data: 'harga_item',
                name: 'harga_item'
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
    $(".content").on("click", ".edit", function() {
        $("#formModal").modal('show');
        $(".modal-title").text('Edit Data');
        $("form").attr("action", `${BASE_URL}/pelanggan/ubah`);

        $("#pelanggan").val($(this).data("pelanggan"));
        $("#jenkel").val($(this).data("jenkel"));
        $("#telp").val($(this).data("telp"));
        $("#alamat").val($(this).data("alamat"));
        $("button[type=submit]").attr("id", "ubah");
        $(".modal-footer").append('<input type="hidden" name="id" value="' + $(this).data("id") + '">');
    })
    // button ubah
    $(".content").on("click", "#ubah", function(e) {
        e.preventDefault();
        $.ajax({
            type: $("form").attr("method"),
            url: $("form").attr("action"),
            dataType: "json",
            data: $("form").serialize(),
            success: function(response) {
                responValidasi(['ubah'], ['pelanggan'], response);
                if (response.sukses) {
                    $('#formModal').modal('hide')
                    table.ajax.reload()
                }
            }
        });
    })
    $('.content').on('click', '.hapus', function(e) {
        Swal.fire({
            title: 'Yakin ingin menghapus data ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Konfirmasi!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${BASE_URL}/pelanggan/hapus`,
                    data: {
                        id: $(this).data('id')
                    },
                    success: function(response) {
                        table.ajax.reload()
                        if (response.status) {
                            toastr.success(response.pesan, 'Sukses');
                        } else {
                            toastr.error(response.pesan, 'Gagal');
                        }
                    }
                })
            }
        })
    })
});
</script>
<?php $this->endSection(); ?>