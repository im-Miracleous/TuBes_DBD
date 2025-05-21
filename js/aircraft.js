document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('aircraft-modal');
    const addBtn = document.getElementById('add-aircraft-btn');
    const closeBtn = document.querySelector('.close');
    const aircraftForm = document.getElementById('aircraft-form');
    const searchBtn = document.getElementById('search-btn');
    
    // Load dropdown data
    loadAirlines();
    
    // Load aircraft data
    fetchAircraft();
    
    // Event listeners
    addBtn.addEventListener('click', () => {
        document.getElementById('modal-title').textContent = 'Add New Aircraft';
        document.getElementById('aircraft-id').value = '';
        aircraftForm.reset();
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
    
    aircraftForm.addEventListener('submit', handleAircraftSubmit);
    searchBtn.addEventListener('click', handleSearch);
    
    // Also trigger search when pressing Enter in search field
    document.getElementById('search-aircraft').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            handleSearch();
        }
    });
});

function fetchAircraft(searchTerm = '') {
    let url = 'php/api/aircraft/read.php';
    if (searchTerm) {
        url += `?search=${encodeURIComponent(searchTerm)}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector('#aircraft-table tbody');
            tableBody.innerHTML = '';
            
            if (data.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = `<td colspan="6" class="no-data">No aircraft found</td>`;
                tableBody.appendChild(row);
                return;
            }
            
            data.forEach(aircraft => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${aircraft.AircraftID}</td>
                    <td>${aircraft.AircraftType}</td>
                    <td>${aircraft.RegistrationNumber}</td>
                    <td>${aircraft.Capacity}</td>
                    <td>${aircraft.AirlineCode} - ${aircraft.AirlineName || ''}</td>
                    <td>
                        <button class="btn-edit" data-id="${aircraft.AircraftID}">Edit</button>
                        <button class="btn-delete" data-id="${aircraft.AircraftID}">Delete</button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
            
            // Add event listeners to edit buttons
            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', handleEditAircraft);
            });
            
            // Add event listeners to delete buttons
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', handleDeleteAircraft);
            });
        })
        .catch(error => {
            console.error('Error fetching aircraft:', error);
            document.querySelector('#aircraft-table tbody').innerHTML = `
                <tr><td colspan="6" class="error">Error loading aircraft. Please try again.</td></tr>
            `;
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

function handleSearch() {
    const searchTerm = document.getElementById('search-aircraft').value.trim();
    fetchAircraft(searchTerm);
}

function handleEditAircraft(e) {
    const aircraftId = e.target.getAttribute('data-id');
    
    fetch(`php/api/aircraft/read.php?id=${aircraftId}`)
        .then(response => response.json())
        .then(aircraft => {
            document.getElementById('modal-title').textContent = 'Edit Aircraft';
            document.getElementById('aircraft-id').value = aircraft.AircraftID;
            document.getElementById('aircraft-type').value = aircraft.AircraftType;
            document.getElementById('registration-number').value = aircraft.RegistrationNumber;
            document.getElementById('capacity').value = aircraft.Capacity;
            document.getElementById('airline-code').value = aircraft.AirlineCode;
            
            document.getElementById('aircraft-modal').style.display = 'block';
        })
        .catch(error => {
            console.error('Error fetching aircraft:', error);
            alert('Error loading aircraft data. Please try again.');
        });
}

function handleDeleteAircraft(e) {
    if (!confirm('Are you sure you want to delete this aircraft? This action cannot be undone.')) return;
    
    const aircraftId = e.target.getAttribute('data-id');
    
    fetch(`php/api/aircraft/delete.php?id=${aircraftId}`, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fetchAircraft();
        } else {
            alert('Error: ' + (data.message || 'Failed to delete aircraft'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the aircraft.');
    });
}

function handleAircraftSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const aircraftId = formData.get('aircraft-id');
    const isEdit = !!aircraftId;

    // Build JSON object with correct keys
    const data = {
        'aircraft-type': formData.get('aircraft-type'),
        'registration-number': formData.get('registration-number'),
        'capacity': formData.get('capacity'),
        'airline-code': formData.get('airline-code')
    };

    const url = isEdit 
        ? `php/api/aircraft/update.php?id=${aircraftId}`
        : 'php/api/aircraft/create.php';
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
            fetchAircraft();
            document.getElementById('aircraft-modal').style.display = 'none';
        } else {
            alert('Error: ' + (data.message || 'Operation failed'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
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