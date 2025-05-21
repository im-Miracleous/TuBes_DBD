document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('airline-modal');
    const addBtn = document.getElementById('add-airline-btn');
    const closeBtn = document.querySelector('.close');
    const airlineForm = document.getElementById('airline-form');
    const searchBtn = document.getElementById('search-btn');
    
    // Load airlines data
    fetchAirlines();
    
    // Event listeners
    addBtn.addEventListener('click', () => {
        document.getElementById('modal-title').textContent = 'Add New Airline';
        document.getElementById('airline-code-original').value = '';
        airlineForm.reset();
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
    
    airlineForm.addEventListener('submit', handleAirlineSubmit);
    searchBtn.addEventListener('click', handleSearch);
    
    // Also trigger search when pressing Enter in search field
    document.getElementById('search-airline').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            handleSearch();
        }
    });
});

function fetchAirlines(searchTerm = '') {
    let url = 'php/api/airline/read.php';
    if (searchTerm) {
        url += `?search=${encodeURIComponent(searchTerm)}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector('#airline-table tbody');
            tableBody.innerHTML = '';
            
            if (data.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = `<td colspan="5" class="no-data">No airlines found</td>`;
                tableBody.appendChild(row);
                return;
            }
            
            data.forEach(airline => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${airline.AirlineCode}</td>
                    <td>${airline.AirlineName}</td>
                    <td>${airline.ContactNumber || '-'}</td>
                    <td>${airline.OperatingRegion || '-'}</td>
                    <td>
                        <button class="btn-edit" data-code="${airline.AirlineCode}">Edit</button>
                        <button class="btn-delete" data-code="${airline.AirlineCode}">Delete</button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
            
            // Add event listeners to edit buttons
            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', handleEditAirline);
            });
            
            // Add event listeners to delete buttons
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', handleDeleteAirline);
            });
        })
        .catch(error => {
            console.error('Error fetching airlines:', error);
            document.querySelector('#airline-table tbody').innerHTML = `
                <tr><td colspan="5" class="error">Error loading airlines. Please try again.</td></tr>
            `;
        });
}

function handleSearch() {
    const searchTerm = document.getElementById('search-airline').value.trim();
    fetchAirlines(searchTerm);
}

function handleEditAirline(e) {
    const airlineCode = e.target.getAttribute('data-code');
    
    fetch(`php/api/airline/read.php?code=${airlineCode}`)
        .then(response => response.json())
        .then(airline => {
            document.getElementById('modal-title').textContent = 'Edit Airline';
            document.getElementById('airline-code-original').value = airline.AirlineCode;
            document.getElementById('airline-code').value = airline.AirlineCode;
            document.getElementById('airline-name').value = airline.AirlineName;
            document.getElementById('contact-number').value = airline.ContactNumber || '';
            document.getElementById('operating-region').value = airline.OperatingRegion || '';
            
            document.getElementById('airline-modal').style.display = 'block';
        })
        .catch(error => {
            console.error('Error fetching airline:', error);
            alert('Error loading airline data. Please try again.');
        });
}

function handleDeleteAirline(e) {
    if (!confirm('Are you sure you want to delete this airline? This action cannot be undone.')) return;
    
    const airlineCode = e.target.getAttribute('data-code');
    
    fetch(`php/api/airline/delete.php?code=${airlineCode}`, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fetchAirlines();
        } else {
            alert('Error: ' + (data.message || 'Failed to delete airline'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the airline.');
    });
}

function handleAirlineSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const originalCode = formData.get('airline-code-original');
    const isEdit = !!originalCode;

    // Convert airline code to uppercase
    const airlineCode = formData.get('airline-code').toUpperCase();

    // Build JSON object with correct keys
    const data = {
        'airline-code': airlineCode,
        'airline-name': formData.get('airline-name'),
        'contact-number': formData.get('contact-number'),
        'operating-region': formData.get('operating-region')
    };

    const url = isEdit 
        ? `php/api/airline/update.php?code=${originalCode}`
        : 'php/api/airline/create.php';
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
            fetchAirlines();
            document.getElementById('airline-modal').style.display = 'none';
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