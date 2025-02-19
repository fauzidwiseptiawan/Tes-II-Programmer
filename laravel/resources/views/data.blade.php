@extends('index')
@section('title', 'Dashboard')
@section('content')
<div class="container">
    <h2>Data Sorting & Filtering</h2>
    <div class="row">
        <div class="col-md-3">
            <select id="sort_by" class="form-control">
                <option value="">Sort By</option>
                <option value="name">Name</option>
                <option value="discount">Discount</option>
            </select>
        </div>
        <div class="col-md-3">
            <select id="order" class="form-control">
                <option value="asc">Ascending</option>
                <option value="desc">Descending</option>
            </select>
        </div>
        <div class="col-md-3">
            <select id="status_filter" class="form-control">
                <option value="">Filter Status</option>
                <option value="1">Approved</option>
                <option value="0">Unapproved</option>
            </select>
        </div>
        <div class="col-md-3">
            <button id="applyFilter" class="btn btn-primary">Apply</button>
        </div>
    </div>

    <table class="table table-bordered mt-3" id="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Discount</th>
                <th>Attachment</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
    <nav>
        <ul class="pagination justify-content-center"></ul>
    </nav>
</div>
@endsection

@push('page-scripts')
<script>
let timeout = null; // Untuk debounce agar request tidak terlalu sering

// Fetch API dengan Async/Await
async function fetchData(page = 1) {
    try {
        let sortBy = $('#sort_by').val();
        let order = $('#order').val();
        let status = $('#status_filter').val();
        let attachment = $('#attachment_filter').val();
        let discount = $('#discount_filter').val();

        const response = await $.ajax({
            url: `/data?page=${page}`,
            type: "GET",
            data: { 
                sort_by: sortBy, 
                order: order, 
                status: status, 
                attachment: attachment, 
                discount: discount 
            }
        });
console.log(response.data)
        renderTable(response.data);
        renderPagination(response, sortBy, order, status, attachment, discount);
    } catch (error) {
        console.error("Error fetching data:", error);
    }
}

// Fungsi untuk menampilkan data ke tabel
function renderTable(data) {
    let tableBody = $("#data-table tbody");
    tableBody.empty();

    if (data.length > 0) {
        data.forEach((item) => {
            let statusText = item.status == 1 ? "Approved" : "Unapproved";
            let attachmentText = item.attachment == 1 ? "Ada" : "Tidak";
            let discountPopup = "";

            if (item.discount > 0 && item.discount < 1000000) {
                discountPopup = "Popup Discount";
            } else if (item.discount >= 1000000) {
                discountPopup = "Popup Discount - Approval Needed";
            }

            tableBody.append(`
                <tr>
                    <td>${item.name}</td>
                    <td>${item.title}</td>
                    <td>${item.discount} (${discountPopup})</td>
                    <td>${attachmentText}</td>
                    <td>${statusText}</td>
                </tr>
            `);
        });
    } else {
        tableBody.append(`<tr><td colspan="4" class="text-center">No data available</td></tr>`);
    }
}

// Fungsi untuk membuat pagination yang sesuai dengan filter
function renderPagination(response, sortBy, order, status, attachment, discount) {
    let pagination = $(".pagination");
    pagination.empty();

    let currentPage = response.current_page;
    let lastPage = response.last_page;
    let maxVisiblePages = 5; // Jumlah halaman yang tampil di tengah

    if (lastPage > 1) {
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(lastPage, startPage + maxVisiblePages - 1);

        // Tombol First
        if (currentPage > 1) {
            pagination.append(`
                <li class="page-item">
                    <a href="#" class="page-link" onclick="fetchData(1, '${sortBy}', '${order}', '${status}', '${attachment}', '${discount}')">&laquo; First</a>
                </li>
            `);
        }

        // Tombol Previous
        if (currentPage > 1) {
            pagination.append(`
                <li class="page-item">
                    <a href="#" class="page-link" onclick="fetchData(${currentPage - 1}, '${sortBy}', '${order}', '${status}', '${attachment}', '${discount}')">&lsaquo; Prev</a>
                </li>
            `);
        }

        // Nomor Halaman (Hanya menampilkan 5 halaman terdekat)
        for (let i = startPage; i <= endPage; i++) {
            pagination.append(`
                <li class="page-item ${currentPage === i ? 'active' : ''}">
                    <a href="#" class="page-link" onclick="fetchData(${i}, '${sortBy}', '${order}', '${status}', '${attachment}', '${discount}')">${i}</a>
                </li>
            `);
        }

        // Tombol Next
        if (currentPage < lastPage) {
            pagination.append(`
                <li class="page-item">
                    <a href="#" class="page-link" onclick="fetchData(${currentPage + 1}, '${sortBy}', '${order}', '${status}', '${attachment}', '${discount}')">Next &rsaquo;</a>
                </li>
            `);
        }

        // Tombol Last
        if (currentPage < lastPage) {
            pagination.append(`
                <li class="page-item">
                    <a href="#" class="page-link" onclick="fetchData(${lastPage}, '${sortBy}', '${order}', '${status}', '${attachment}', '${discount}')">Last &raquo;</a>
                </li>
            `);
        }
    }
}

// Fungsi untuk memanggil fetchData dengan Debounce (agar tidak terlalu sering)
function applyFilter() {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        fetchData(1); // Reset ke halaman 1 saat filter berubah
    }, 500);
}

// Event Listener untuk Filter
$("#applyFilter").click(applyFilter);
$("#sort_by, #order, #status_filter, #attachment_filter, #discount_filter").change(applyFilter);

// Panggil fetchData pertama kali
$(document).ready(() => fetchData());

</script>
@endpush