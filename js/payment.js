document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('payment-modal');
    const addBtn = document.getElementById('add-payment-btn');
    const closeBtn = document.querySelector('.close');
    const paymentForm = document.getElementById('payment-form');
    const applyFilterBtn = document.getElementById('apply-filter');
    
    // Load dropdown data
    loadBookings();
    
    // Load payment data
    fetchPayments();
    
    // Event listeners
    addBtn.addEventListener('click', () => {
        document.getElementById('modal-title').textContent = 'Add New Payment';
        document.getElementById('payment-id').value = '';
        paymentForm.reset();
        document.getElementById('transaction-date').value = new Date().toISOString().slice(0, 16);
        modal.style.display = 'block';
    });
    
    closeBtn.addEventListener('click', () => {
        modal.style.display = 'none';
    });
    
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    paymentForm.addEventListener('submit', handlePaymentSubmit);
    applyFilterBtn.addEventListener('click', applyFilters);
});

function fetchPayments(filters = {}) {
    let url = 'php/api/payment/read.php';
    const queryParams = new URLSearchParams();
    
    if (filters.dateFrom) queryParams.append('date_from', filters.dateFrom);
    if (filters.dateTo) queryParams.append('date_to', filters.dateTo);
    if (filters.method) queryParams.append('method', filters.method);
    
    if (queryParams.toString()) {
        url += `?${queryParams.toString()}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector('#payment-table tbody');
            tableBody.innerHTML = '';
            
            if (data.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = `<td colspan="7" class="no-data">No payments found</td>`;
                tableBody.appendChild(row);
                return;
            }
            
            data.forEach(payment => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${payment.PaymentID}</td>
                    <td>${payment.BookingID}</td>
                    <td>${payment.PassengerName || 'N/A'}</td>
                    <td>${formatCurrency(payment.Amount)}</td>
                    <td>${payment.PaymentMethod}</td>
                    <td>${formatDateTime(payment.TransactionDateTime)}</td>
                    <td>
                        <button class="btn-edit" data-id="${payment.PaymentID}">Edit</button>
                        <button class="btn-delete" data-id="${payment.PaymentID}">Delete</button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
            
            // Add event listeners to edit buttons
            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', handleEditPayment);
            });
            
            // Add event listeners to delete buttons
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', handleDeletePayment);
            });
        })
        .catch(error => {
            console.error('Error fetching payments:', error);
            document.querySelector('#payment-table tbody').innerHTML = `
                <tr><td colspan="7" class="error">Error loading payments. Please try again.</td></tr>
            `;
        });
}

function loadBookings() {
    fetch('php/api/booking/read.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('booking-id');
            // Clear existing options except the first one
            while (select.options.length > 1) {
                select.remove(1);
            }
            
            data.forEach(booking => {
                const option = document.createElement('option');
                option.value = booking.BookingID;
                option.textContent = `Booking #${booking.BookingID} - ${booking.PassengerName || 'Unknown Passenger'}`;
                select.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading bookings:', error));
}

function applyFilters() {
    const dateFrom = document.getElementById('date-from-filter').value;
    const dateTo = document.getElementById('date-to-filter').value;
    const method = document.getElementById('method-filter').value;
    
    fetchPayments({
        dateFrom: dateFrom || undefined,
        dateTo: dateTo || undefined,
        method: method || undefined
    });
}

function handleEditPayment(e) {
    const paymentId = e.target.getAttribute('data-id');
    
    fetch(`php/api/payment/read.php?id=${paymentId}`)
        .then(response => response.json())
        .then(payment => {
            document.getElementById('modal-title').textContent = 'Edit Payment';
            document.getElementById('payment-id').value = payment.PaymentID;
            document.getElementById('booking-id').value = payment.BookingID;
            document.getElementById('amount').value = payment.Amount;
            document.getElementById('payment-method').value = payment.PaymentMethod;
            document.getElementById('transaction-date').value = formatDateTimeForInput(payment.TransactionDateTime);
            
            document.getElementById('payment-modal').style.display = 'block';
        })
        .catch(error => {
            console.error('Error fetching payment:', error);
            alert('Error loading payment data. Please try again.');
        });
}

function handleDeletePayment(e) {
    if (!confirm('Are you sure you want to delete this payment? This action cannot be undone.')) return;
    
    const paymentId = e.target.getAttribute('data-id');
    
    fetch(`php/api/payment/delete.php?id=${paymentId}`, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fetchPayments();
        } else {
            alert('Error: ' + (data.message || 'Failed to delete payment'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the payment.');
    });
}

function handlePaymentSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const paymentId = formData.get('payment-id');
    const isEdit = !!paymentId;
    
    const url = isEdit 
        ? `php/api/payment/update.php?id=${paymentId}`
        : 'php/api/payment/create.php';
    const method = isEdit ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fetchPayments();
            document.getElementById('payment-modal').style.display = 'none';
        } else {
            alert('Error: ' + (data.message || 'Operation failed'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

// Helper functions
function formatDateTime(datetime) {
    if (!datetime) return '';
    const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit', 
        minute: '2-digit'
    };
    return new Date(datetime).toLocaleString('en-US', options);
}

function formatDateTimeForInput(datetime) {
    if (!datetime) return '';
    const date = new Date(datetime);
    const offset = date.getTimezoneOffset() * 60000;
    return new Date(date.getTime() - offset).toISOString().slice(0, 16);
}

// function formatCurrency(amount) {
//     return new Intl.NumberFormat('en-US', {
//         style: 'currency',
//         currency: 'USD'
//     }).format(amount);
// }

function formatCurrency(amount) {
    return 'Rp ' + Number(amount).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}