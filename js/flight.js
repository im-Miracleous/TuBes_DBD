document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('flight-modal');
    const addBtn = document.getElementById('add-flight-btn');
    const closeBtn = document.querySelector('.close');
    const flightForm = document.getElementById('flight-form');
    const applyFilterBtn = document.getElementById('apply-filter');
    
    // Load dropdown data
    loadAirlines();
    loadAirports();
    
    // Load flights data
    fetchFlights();
    
    // Event listeners
    addBtn.addEventListener('click', () => {
        document.getElementById('modal-title').textContent = 'Add New Flight';
        document.getElementById('flight-id').value = '';
        flightForm.reset();
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
    
    flightForm.addEventListener('submit', handleFlightSubmit);
    applyFilterBtn.addEventListener('click', applyFilters);
});

function fetchFlights(filters = {}) {
    let url = 'php/api/flight/read.php';
    const queryParams = new URLSearchParams();
    
    if (filters.status) queryParams.append('status', filters.status);
    if (filters.date) queryParams.append('date', filters.date);
    
    if (queryParams.toString()) {
        url += `?${queryParams.toString()}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector('#flight-table tbody');
            tableBody.innerHTML = '';
            
            if (data.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = `<td colspan="9" class="no-data">No flights found</td>`;
                tableBody.appendChild(row);
                return;
            }
            
            data.forEach(flight => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${flight.FlightNumber}</td>
                    <td>${flight.AirlineCode} - ${flight.AirlineName || ''}</td>
                    <td>${formatDateTime(flight.DepartureDateTime)}</td>
                    <td>${formatDateTime(flight.ArrivalDateTime)}</td>
                    <td>${flight.OriginName} (${flight.OriginAirportCode})</td>
                    <td>${flight.DestinationName} (${flight.DestinationAirportCode})</td>
                    <td>${flight.AvailableSeats}</td>
                    <td><span class="status-badge ${getStatusClass(flight.Status)}">${translateStatus(flight.Status)}</span></td>
                    <td>
                        <button class="btn-edit" data-id="${flight.FlightID}">Edit</button>
                        <button class="btn-delete" data-id="${flight.FlightID}">Delete</button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
            
            // Add event listeners to edit buttons
            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', handleEditFlight);
            });
            
            // Add event listeners to delete buttons
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', handleDeleteFlight);
            });
        })
        .catch(error => {
            console.error('Error fetching flights:', error);
            document.querySelector('#flight-table tbody').innerHTML = `
                <tr><td colspan="9" class="error">Error loading flights. Please try again.</td></tr>
            `;
        });
}

function applyFilters() {
    const status = document.getElementById('status-filter').value;
    const date = document.getElementById('date-filter').value;
    
    fetchFlights({
        status: status || undefined,
        date: date || undefined
    });
}

function handleEditFlight(e) {
    const flightId = e.target.getAttribute('data-id');
    
    fetch(`php/api/flight/read.php?id=${flightId}`)
        .then(response => response.json())
        .then(flight => {
            document.getElementById('modal-title').textContent = 'Edit Flight';
            document.getElementById('flight-id').value = flight.FlightID;
            document.getElementById('flight-number').value = flight.FlightNumber;
            document.getElementById('airline-code').value = flight.AirlineCode;
            document.getElementById('departure-datetime').value = formatDateTimeForInput(flight.DepartureDateTime);
            document.getElementById('arrival-datetime').value = formatDateTimeForInput(flight.ArrivalDateTime);
            document.getElementById('origin-airport').value = flight.OriginAirportCode;
            document.getElementById('destination-airport').value = flight.DestinationAirportCode;
            document.getElementById('available-seats').value = flight.AvailableSeats;
            document.getElementById('status').value = flight.Status;
            
            document.getElementById('flight-modal').style.display = 'block';
        })
        .catch(error => {
            console.error('Error fetching flight:', error);
            alert('Error loading flight data. Please try again.');
        });
}

function handleDeleteFlight(e) {
    if (!confirm('Are you sure you want to delete this flight? This action cannot be undone.')) return;
    
    const flightId = e.target.getAttribute('data-id');
    
    fetch(`php/api/flight/delete.php?id=${flightId}`, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fetchFlights();
        } else {
            alert('Error: ' + (data.message || 'Failed to delete flight'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the flight.');
    });
}

function handleFlightSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const flightId = formData.get('flight-id');
    const isEdit = !!flightId;

    // Validate departure before arrival
    const departure = new Date(formData.get('departure-datetime'));
    const arrival = new Date(formData.get('arrival-datetime'));
    if (departure >= arrival) {
        alert('Arrival must be after departure');
        return;
    }

    // Convert FormData to JSON object with correct keys
    const data = {
        'flight-number': formData.get('flight-number'),
        'airline-code': formData.get('airline-code'),
        'departure-datetime': formData.get('departure-datetime'),
        'arrival-datetime': formData.get('arrival-datetime'),
        'origin-airport': formData.get('origin-airport'),
        'destination-airport': formData.get('destination-airport'),
        'available-seats': formData.get('available-seats'),
        'status': formData.get('status')
    };

    console.log('Submitting flight data:', data);
    const url = isEdit 
        ? `php/api/flight/update.php?id=${flightId}`
        : 'php/api/flight/create.php';
    const method = isEdit ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fetchFlights();
            document.getElementById('flight-modal').style.display = 'none';
        } else {
            alert('Error: ' + (data.message || 'Operation failed'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

function loadAirlines() {
    fetch('php/api/airline/read.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('airline-code');
            // Clear existing options except the first one
            while (select.options.length > 1) {
                select.remove(1);
            }
            
            data.forEach(airline => {
                const option = document.createElement('option');
                option.value = airline.AirlineCode;
                option.textContent = `${airline.AirlineName} (${airline.AirlineCode})`;
                select.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading airlines:', error));
}

function loadAirports() {
    fetch('php/api/airport/read.php')
        .then(response => response.json())
        .then(data => {
            const originSelect = document.getElementById('origin-airport');
            const destSelect = document.getElementById('destination-airport');
            
            // Clear existing options except the first one
            while (originSelect.options.length > 1) originSelect.remove(1);
            while (destSelect.options.length > 1) destSelect.remove(1);
            
            data.forEach(airport => {
                const option = document.createElement('option');
                option.value = airport.AirportCode;
                option.textContent = `${airport.AirportName} (${airport.AirportCode})`;
                
                originSelect.appendChild(option.cloneNode(true));
                destSelect.appendChild(option.cloneNode(true));
            });
        })
        .catch(error => console.error('Error loading airports:', error));
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

function translateStatus(status) {
    const translations = {
        'Terjadwal': 'Scheduled',
        'Ditunda': 'Delayed',
        'Dibatalkan': 'Cancelled'
    };
    return translations[status] || status;
}

function getStatusClass(status) {
    const classes = {
        'Terjadwal': 'status-scheduled',
        'Ditunda': 'status-delayed',
        'Dibatalkan': 'status-cancelled'
    };
    return classes[status] || '';
}

// Responsive nav toggle
const navToggle = document.getElementById('nav-toggle');
const navLinks = document.getElementById('nav-links');

if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => {
        navLinks.classList.toggle('active');
        navToggle.classList.toggle('active'); // Toggle active class for hamburger/arrow
    });
    // Only close nav on mobile if menu is open
    navLinks.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768 && navLinks.classList.contains('active')) {
                navLinks.classList.remove('active');
                navToggle.classList.remove('active'); // Remove active from toggle on close
            }
        });
    });
}