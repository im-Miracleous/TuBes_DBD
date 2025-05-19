document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('passenger-modal');
    const addBtn = document.getElementById('add-passenger-btn');
    const closeBtn = document.querySelector('.close');
    const passengerForm = document.getElementById('passenger-form');
    const searchBtn = document.getElementById('search-btn');
    
    // Load passengers data
    fetchPassengers();
    
    // Event listeners
    addBtn.addEventListener('click', () => {
        document.getElementById('modal-title').textContent = 'Add New Passenger';
        document.getElementById('passenger-id').value = '';
        passengerForm.reset();
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
    
    passengerForm.addEventListener('submit', handlePassengerSubmit);
    searchBtn.addEventListener('click', handleSearch);
    
    // Also trigger search when pressing Enter in search field
    document.getElementById('search-passenger').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            handleSearch();
        }
    });
});

function fetchPassengers(searchTerm = '') {
    let url = 'php/api/passenger/read.php';
    if (searchTerm) {
        url += `?search=${encodeURIComponent(searchTerm)}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector('#passenger-table tbody');
            tableBody.innerHTML = '';
            
            if (data.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = `<td colspan="6" class="no-data">No passengers found</td>`;
                tableBody.appendChild(row);
                return;
            }
            
            data.forEach(passenger => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${passenger.PassengerID}</td>
                    <td>${passenger.FirstName}</td>
                    <td>${passenger.LastName}</td>
                    <td>${passenger.Email}</td>
                    <td>${passenger.PassportNumber}</td>
                    <td>
                        <button class="btn-edit" data-id="${passenger.PassengerID}">Edit</button>
                        <button class="btn-delete" data-id="${passenger.PassengerID}">Delete</button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
            
            // Add event listeners to edit buttons
            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', handleEditPassenger);
            });
            
            // Add event listeners to delete buttons
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', handleDeletePassenger);
            });
        })
        .catch(error => {
            console.error('Error fetching passengers:', error);
            document.querySelector('#passenger-table tbody').innerHTML = `
                <tr><td colspan="6" class="error">Error loading passengers. Please try again.</td></tr>
            `;
        });
}

function handleSearch() {
    const searchTerm = document.getElementById('search-passenger').value.trim();
    fetchPassengers(searchTerm);
}

function handleEditPassenger(e) {
    const passengerId = e.target.getAttribute('data-id');
    
    fetch(`php/api/passenger/read.php?id=${passengerId}`)
        .then(response => response.json())
        .then(passenger => {
            document.getElementById('modal-title').textContent = 'Edit Passenger';
            document.getElementById('passenger-id').value = passenger.PassengerID;
            document.getElementById('first-name').value = passenger.FirstName;
            document.getElementById('last-name').value = passenger.LastName;
            document.getElementById('email').value = passenger.Email;
            document.getElementById('passport-number').value = passenger.PassportNumber;
            
            document.getElementById('passenger-modal').style.display = 'block';
        })
        .catch(error => {
            console.error('Error fetching passenger:', error);
            alert('Error loading passenger data. Please try again.');
        });
}

function handleDeletePassenger(e) {
    if (!confirm('Are you sure you want to delete this passenger? This action cannot be undone.')) return;
    
    const passengerId = e.target.getAttribute('data-id');
    
    fetch(`php/api/passenger/delete.php?id=${passengerId}`, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fetchPassengers();
        } else {
            alert('Error: ' + (data.message || 'Failed to delete passenger'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the passenger.');
    });
}

function handlePassengerSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const passengerId = formData.get('passenger-id');
    const isEdit = !!passengerId;
    
    const url = isEdit 
        ? `php/api/passenger/update.php?id=${passengerId}`
        : 'php/api/passenger/create.php';
    const method = isEdit ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fetchPassengers();
            document.getElementById('passenger-modal').style.display = 'none';
        } else {
            alert('Error: ' + (data.message || 'Operation failed'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}