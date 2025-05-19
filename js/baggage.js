document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('baggage-modal');
    const addBtn = document.getElementById('add-baggage-btn');
    const closeBtn = document.querySelector('.close');
    const baggageForm = document.getElementById('baggage-form');
    const applyFilterBtn = document.getElementById('apply-filter');
    
    // Load dropdown data
    loadBookings();
    
    // Load baggage data
    fetchBaggage();
    
    // Event listeners
    addBtn.addEventListener('click', () => {
        document.getElementById('modal-title').textContent = 'Add New Baggage';
        document.getElementById('baggage-id').value = '';
        baggageForm.reset();
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
    
    baggageForm.addEventListener('submit', handleBaggageSubmit);
    applyFilterBtn.addEventListener('click', applyFilters);
});

function fetchBaggage(filters = {}) {
    let url = 'php/api/baggage/read.php';
    const queryParams = new URLSearchParams();
    
    if (filters.status) queryParams.append('status', filters.status);
    if (filters.type) queryParams.append('type', filters.type);
    
    if (queryParams.toString()) {
        url += `?${queryParams.toString()}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector('#baggage-table tbody');
            tableBody.innerHTML = '';
            
            if (data.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = `<td colspan="7" class="no-data">No baggage found</td>`;
                tableBody.appendChild(row);
                return;
            }
            
            data.forEach(baggage => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${baggage.BaggageID}</td>
                    <td>${baggage.BookingID}</td>
                    <td>${baggage.PassengerName || 'N/A'}</td>
                    <td>${baggage.Weight}</td>
                    <td>${baggage.BaggageType}</td>
                    <td><span class="status-badge ${getStatusClass(baggage.Status)}">${baggage.Status}</span></td>
                    <td>
                        <button class="btn-edit" data-id="${baggage.BaggageID}">Edit</button>
                        <button class="btn-delete" data-id="${baggage.BaggageID}">Delete</button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
            
            // Add event listeners to edit buttons
            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', handleEditBaggage);
            });
            
            // Add event listeners to delete buttons
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', handleDeleteBaggage);
            });
        })
        .catch(error => {
            console.error('Error fetching baggage:', error);
            document.querySelector('#baggage-table tbody').innerHTML = `
                <tr><td colspan="7" class="error">Error loading baggage. Please try again.</td></tr>
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
    const status = document.getElementById('status-filter').value;
    const type = document.getElementById('type-filter').value;
    
    fetchBaggage({
        status: status || undefined,
        type: type || undefined
    });
}

function handleEditBaggage(e) {
    const baggageId = e.target.getAttribute('data-id');
    
    fetch(`php/api/baggage/read.php?id=${baggageId}`)
        .then(response => response.json())
        .then(baggage => {
            document.getElementById('modal-title').textContent = 'Edit Baggage';
            document.getElementById('baggage-id').value = baggage.BaggageID;
            document.getElementById('booking-id').value = baggage.BookingID;
            document.getElementById('weight').value = baggage.Weight;
            document.getElementById('baggage-type').value = baggage.BaggageType;
            document.getElementById('status').value = baggage.Status;
            
            document.getElementById('baggage-modal').style.display = 'block';
        })
        .catch(error => {
            console.error('Error fetching baggage:', error);
            alert('Error loading baggage data. Please try again.');
        });
}

function handleDeleteBaggage(e) {
    if (!confirm('Are you sure you want to delete this baggage record? This action cannot be undone.')) return;
    
    const baggageId = e.target.getAttribute('data-id');
    
    fetch(`php/api/baggage/delete.php?id=${baggageId}`, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fetchBaggage();
        } else {
            alert('Error: ' + (data.message || 'Failed to delete baggage'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the baggage record.');
    });
}

function handleBaggageSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const baggageId = formData.get('baggage-id');
    const isEdit = !!baggageId;
    
    const url = isEdit 
        ? `php/api/baggage/update.php?id=${baggageId}`
        : 'php/api/baggage/create.php';
    const method = isEdit ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fetchBaggage();
            document.getElementById('baggage-modal').style.display = 'none';
        } else {
            alert('Error: ' + (data.message || 'Operation failed'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

function getStatusClass(status) {
    const classes = {
        'Checked In': 'status-checked-in',
        'Onboard': 'status-onboard',
        'In Transit': 'status-in-transit',
        'Lost': 'status-lost'
    };
    return classes[status] || '';
}