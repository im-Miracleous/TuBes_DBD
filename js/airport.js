document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('airport-modal');
    const addBtn = document.getElementById('add-airport-btn');
    const closeBtn = document.querySelector('.close');
    const airportForm = document.getElementById('airport-form');
    const searchBtn = document.getElementById('search-btn');
    
    // Load airports data
    fetchAirports();
    
    // Event listeners
    addBtn.addEventListener('click', () => {
        document.getElementById('modal-title').textContent = 'Add New Airport';
        document.getElementById('airport-code-original').value = '';
        airportForm.reset();
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
    
    airportForm.addEventListener('submit', handleAirportSubmit);
    searchBtn.addEventListener('click', handleSearch);
    
    // Also trigger search when pressing Enter in search field
    document.getElementById('search-airport').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            handleSearch();
        }
    });
});

function fetchAirports(searchTerm = '') {
    let url = 'php/api/airport/read.php';
    if (searchTerm) {
        url += `?search=${encodeURIComponent(searchTerm)}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector('#airport-table tbody');
            tableBody.innerHTML = '';
            
            if (data.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = `<td colspan="5" class="no-data">No airports found</td>`;
                tableBody.appendChild(row);
                return;
            }
            
            data.forEach(airport => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${airport.AirportCode}</td>
                    <td>${airport.AirportName}</td>
                    <td>${airport.City}</td>
                    <td>${airport.Country}</td>
                    <td>
                        <button class="btn-edit" data-code="${airport.AirportCode}">Edit</button>
                        <button class="btn-delete" data-code="${airport.AirportCode}">Delete</button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
            
            // Add event listeners to edit buttons
            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', handleEditAirport);
            });
            
            // Add event listeners to delete buttons
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', handleDeleteAirport);
            });
        })
        .catch(error => {
            console.error('Error fetching airports:', error);
            document.querySelector('#airport-table tbody').innerHTML = `
                <tr><td colspan="5" class="error">Error loading airports. Please try again.</td></tr>
            `;
        });
}

function handleSearch() {
    const searchTerm = document.getElementById('search-airport').value.trim();
    fetchAirports(searchTerm);
}

function handleEditAirport(e) {
    const airportCode = e.target.getAttribute('data-code');
    
    fetch(`php/api/airport/read.php?code=${airportCode}`)
        .then(response => response.json())
        .then(airport => {
            document.getElementById('modal-title').textContent = 'Edit Airport';
            document.getElementById('airport-code-original').value = airport.AirportCode;
            document.getElementById('airport-code').value = airport.AirportCode;
            document.getElementById('airport-name').value = airport.AirportName;
            document.getElementById('city').value = airport.City;
            document.getElementById('country').value = airport.Country;
            
            document.getElementById('airport-modal').style.display = 'block';
        })
        .catch(error => {
            console.error('Error fetching airport:', error);
            alert('Error loading airport data. Please try again.');
        });
}

function handleDeleteAirport(e) {
    if (!confirm('Are you sure you want to delete this airport? This action cannot be undone.')) return;
    
    const airportCode = e.target.getAttribute('data-code');
    
    fetch(`php/api/airport/delete.php?code=${airportCode}`, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fetchAirports();
        } else {
            alert('Error: ' + (data.message || 'Failed to delete airport'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the airport.');
    });
}

function handleAirportSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const originalCode = formData.get('airport-code-original');
    const isEdit = !!originalCode;

    // Convert airport code to uppercase
    const airportCode = formData.get('airport-code').toUpperCase();

    // Build JSON object with correct keys
    const data = {
        'airport-code': airportCode,
        'airport-name': formData.get('airport-name'),
        'city': formData.get('city'),
        'country': formData.get('country')
    };

    const url = isEdit 
        ? `php/api/airport/update.php?code=${originalCode}`
        : 'php/api/airport/create.php';
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
            fetchAirports();
            document.getElementById('airport-modal').style.display = 'none';
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