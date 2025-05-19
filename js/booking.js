document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('booking-modal');
    const addBtn = document.getElementById('add-booking-btn');
    const closeBtn = document.querySelector('.close');
    const bookingForm = document.getElementById('booking-form');
    const applyFilterBtn = document.getElementById('apply-filter');
    
    // Load dropdown data
    loadFlights();
    loadPassengers();
    
    // Load bookings data
    fetchBookings();
    
    // Event listeners
    addBtn.addEventListener('click', () => {
        document.getElementById('modal-title').textContent = 'Add New Booking';
        document.getElementById('booking-id').value = '';
        bookingForm.reset();
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
    
    bookingForm.addEventListener('submit', handleBookingSubmit);
    applyFilterBtn.addEventListener('click', applyFilters);
});

function fetchBookings(filters = {}) {
    let url = 'php/api/booking/read.php';
    const queryParams = new URLSearchParams();
    
    if (filters.status) queryParams.append('status', filters.status);
    if (filters.date) queryParams.append('date', filters.date);
    
    if (queryParams.toString()) {
        url += `?${queryParams.toString()}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector('#booking-table tbody');
            tableBody.innerHTML = '';
            
            if (data.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = `<td colspan="6" class="no-data">No bookings found</td>`;
                tableBody.appendChild(row);
                return;
            }
            
            data.forEach(booking => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${booking.BookingID}</td>
                    <td>${booking.FlightNumber} (${booking.AirlineCode})</td>
                    <td>${booking.PassengerName} (${booking.PassportNumber})</td>
                    <td>${formatDate(booking.BookingDate)}</td>
                    <td><span class="status-badge ${getPaymentStatusClass(booking.PaymentStatus)}">${booking.PaymentStatus}</span></td>
                    <td>
                        <button class="btn-edit" data-id="${booking.BookingID}">Edit</button>
                        <button class="btn-delete" data-id="${booking.BookingID}">Delete</button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
            
            // Add event listeners to edit buttons
            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', handleEditBooking);
            });
            
            // Add event listeners to delete buttons
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', handleDeleteBooking);
            });
        })
        .catch(error => {
            console.error('Error fetching bookings:', error);
            document.querySelector('#booking-table tbody').innerHTML = `
                <tr><td colspan="6" class="error">Error loading bookings. Please try again.</td></tr>
            `;
        });
}

function applyFilters() {
    const status = document.getElementById('status-filter').value;
    const date = document.getElementById('date-filter').value;
    
    fetchBookings({
        status: status || undefined,
        date: date || undefined
    });
}

function loadFlights() {
    fetch('php/api/flight/read.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('flight-id');
            // Clear existing options except the first one
            while (select.options.length > 1) {
                select.remove(1);
            }
            
            data.forEach(flight => {
                const option = document.createElement('option');
                option.value = flight.FlightID;
                option.textContent = `${flight.FlightNumber} (${flight.AirlineCode}) - ${formatDate(flight.DepartureDateTime)}`;
                select.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading flights:', error));
}

function loadPassengers() {
    fetch('php/api/passenger/read.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('passenger-id');
            // Clear existing options except the first one
            while (select.options.length > 1) {
                select.remove(1);
            }
            
            data.forEach(passenger => {
                const option = document.createElement('option');
                option.value = passenger.PassengerID;
                option.textContent = `${passenger.FirstName} ${passenger.LastName} (${passenger.PassportNumber})`;
                select.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading passengers:', error));
}

function handleEditBooking(e) {
    const bookingId = e.target.getAttribute('data-id');
    
    fetch(`php/api/booking/read.php?id=${bookingId}`)
        .then(response => response.json())
        .then(booking => {
            document.getElementById('modal-title').textContent = 'Edit Booking';
            document.getElementById('booking-id').value = booking.BookingID;
            document.getElementById('flight-id').value = booking.FlightID;
            document.getElementById('passenger-id').value = booking.PassengerID;
            document.getElementById('payment-status').value = booking.PaymentStatus;
            
            document.getElementById('booking-modal').style.display = 'block';
        })
        .catch(error => {
            console.error('Error fetching booking:', error);
            alert('Error loading booking data. Please try again.');
        });
}

function handleDeleteBooking(e) {
    if (!confirm('Are you sure you want to delete this booking? This action cannot be undone.')) return;
    
    const bookingId = e.target.getAttribute('data-id');
    
    fetch(`php/api/booking/delete.php?id=${bookingId}`, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fetchBookings();
        } else {
            alert('Error: ' + (data.message || 'Failed to delete booking'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the booking.');
    });
}

function handleBookingSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const bookingId = formData.get('booking-id');
    const isEdit = !!bookingId;
    
    const url = isEdit 
        ? `php/api/booking/update.php?id=${bookingId}`
        : 'php/api/booking/create.php';
    const method = isEdit ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fetchBookings();
            document.getElementById('booking-modal').style.display = 'none';
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
function formatDate(dateString) {
    if (!dateString) return '';
    const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit', 
        minute: '2-digit'
    };
    return new Date(dateString).toLocaleString('en-US', options);
}

function getPaymentStatusClass(status) {
    const classes = {
        'Pending': 'status-pending',
        'Paid': 'status-paid',
        'Cancelled': 'status-cancelled',
        'Rescheduled': 'status-rescheduled'
    };
    return classes[status] || '';
}