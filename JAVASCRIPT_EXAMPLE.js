/**
 * ========================================
 * JAVASCRIPT/JQUERY - FRONTEND LOGIC
 * ========================================
 * File: resources/views/jual/detail_jual.blade.php
 * Section: @section('js')
 */

<script>
$(document).ready(function() {
    var CSRF_TOKEN = $('input[name="_token"]').val();
    
    /**
     * ========================================
     * EVENT: TOMBOL SIMPAN/CETAK DIKLIK
     * ========================================
     */
    $(".simpan").click(function() {
        let dataBarang = [];

        // ============================================
        // STEP 1: Ambil semua data dari tabel
        // ============================================
        $("#table1 tbody tr").each(function() {
            var currow = $(this);
            
            dataBarang.push({
                'barang_id': currow.find('td:eq(1)').text().trim(),
                'qty': currow.find('td:eq(3)').text().trim(),
                'harga_sekarang': currow.find('td:eq(5)').text().trim(),
                'jual_id': "{{$id}}"
            });
        });

        // ============================================
        // STEP 2: Validasi - Cek apakah ada barang
        // ============================================
        if (dataBarang.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Keranjang Kosong',
                text: 'Belum ada barang yang ditambahkan ke daftar belanja!'
            });
            // Atau gunakan alert biasa:
            // alert('Belum ada barang yang ditambahkan ke daftar belanja!');
            return false;
        }

        // ============================================
        // STEP 3: Konfirmasi sebelum simpan
        // ============================================
        const totalItem = dataBarang.length;
        const konfirmasi = confirm(
            '🛒 Konfirmasi Transaksi\n\n' +
            '━━━━━━━━━━━━━━━━━━━━━━━━\n' +
            '📦 Total Item: ' + totalItem + ' barang\n' +
            '💰 Total: Rp ' + $("#jtotal").val() + '\n' +
            '📉 Stok akan dikurangi otomatis\n' +
            '━━━━━━━━━━━━━━━━━━━━━━━━\n\n' +
            'Lanjutkan transaksi ini?'
        );
        
        if (!konfirmasi) {
            return false;
        }

        // ============================================
        // STEP 4: Disable tombol (prevent double click)
        // ============================================
        const tombolSimpan = $(this);
        tombolSimpan.prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        // ============================================
        // STEP 5: Kirim data ke server via AJAX
        // ============================================
        $.ajax({
            url: '/jual/simpan',
            type: 'POST',
            data: {
                _token: CSRF_TOKEN,
                idJual: "{{ $id }}",
                dataBarang: dataBarang
            },
            beforeSend: function() {
                console.log('📤 Mengirim data:', dataBarang);
            },
            success: function(response) {
                console.log('📥 Response:', response);
                
                if (response.berhasil) {
                    // Success notification
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Transaksi berhasil disimpan. Stok barang telah dikurangi.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Redirect ke halaman cetak
                        window.location.href = response.urlCetak;
                    });
                    
                    // Atau gunakan alert biasa:
                    // alert('✅ Transaksi berhasil disimpan!\nStok barang telah dikurangi.');
                    // window.location.href = response.urlCetak;
                } else {
                    throw new Error(response.message || 'Gagal menyimpan transaksi');
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Error:', xhr.responseJSON);
                
                const errorMessage = xhr.responseJSON?.message || 
                                   'Terjadi kesalahan saat menyimpan transaksi';
                
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: errorMessage
                });
                
                // Atau gunakan alert biasa:
                // alert('❌ Error: ' + errorMessage);
                
                // Re-enable tombol
                tombolSimpan.prop('disabled', false)
                           .html('<i class="fas fa-save"></i> Simpan/Cetak');
            }
        });
    });
});
</script>


/**
 * ========================================
 * ALTERNATIVE: MENGGUNAKAN FETCH API
 * ========================================
 * Modern JavaScript (ES6+)
 */
<script>
document.querySelector('.simpan').addEventListener('click', async function() {
    // Ambil data dari tabel
    const rows = document.querySelectorAll('#table1 tbody tr');
    const dataBarang = [];
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        dataBarang.push({
            barang_id: cells[1].textContent.trim(),
            qty: cells[3].textContent.trim(),
            harga_sekarang: cells[5].textContent.trim(),
            jual_id: "{{$id}}"
        });
    });
    
    // Validasi
    if (dataBarang.length === 0) {
        alert('Keranjang kosong!');
        return;
    }
    
    // Konfirmasi
    if (!confirm('Simpan transaksi ini?')) {
        return;
    }
    
    // Disable tombol
    this.disabled = true;
    this.textContent = 'Menyimpan...';
    
    try {
        // Kirim request
        const response = await fetch('/jual/simpan', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: JSON.stringify({
                idJual: "{{$id}}",
                dataBarang: dataBarang
            })
        });
        
        const result = await response.json();
        
        if (result.berhasil) {
            alert('✅ Transaksi berhasil!');
            window.location.href = result.urlCetak;
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        alert('❌ Error: ' + error.message);
        this.disabled = false;
        this.textContent = 'Simpan/Cetak';
    }
});
</script>


/**
 * ========================================
 * DENGAN SWEET ALERT 2 (Lebih Cantik)
 * ========================================
 * Include: <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 */
<script>
$(".simpan").click(async function() {
    let dataBarang = [];
    
    // Ambil data
    $("#table1 tbody tr").each(function() {
        var currow = $(this);
        dataBarang.push({
            'barang_id': currow.find('td:eq(1)').text().trim(),
            'qty': currow.find('td:eq(3)').text().trim(),
            'harga_sekarang': currow.find('td:eq(5)').text().trim(),
        });
    });
    
    if (dataBarang.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Keranjang Kosong',
            text: 'Belum ada barang di keranjang!'
        });
        return;
    }
    
    // Konfirmasi dengan SweetAlert
    const result = await Swal.fire({
        title: 'Konfirmasi Transaksi',
        html: `
            <div style="text-align: left; padding: 10px;">
                <p>📦 Total Item: <strong>${dataBarang.length} barang</strong></p>
                <p>💰 Total: <strong>Rp ${$("#jtotal").val()}</strong></p>
                <p>📉 Stok akan dikurangi otomatis</p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '✅ Ya, Simpan!',
        cancelButtonText: '❌ Batal'
    });
    
    if (!result.isConfirmed) return;
    
    // Loading
    Swal.fire({
        title: 'Menyimpan...',
        html: 'Mohon tunggu, sedang memproses transaksi',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // AJAX Request
    $.ajax({
        url: '/jual/simpan',
        type: 'POST',
        data: {
            _token: $('input[name="_token"]').val(),
            idJual: "{{$id}}",
            dataBarang: dataBarang
        },
        success: function(response) {
            if (response.berhasil) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Transaksi berhasil disimpan',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = response.urlCetak;
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: xhr.responseJSON?.message || 'Terjadi kesalahan'
            });
        }
    });
});
</script>
