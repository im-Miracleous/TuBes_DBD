document.addEventListener('DOMContentLoaded', function() {
    loadDashboardStats();
    loadRecentFlights();
});

function loadDashboardStats() {
    // Load upcoming flights count
    fetch('php/api/flight/read.php?status=Terjadwal')
        .then(response => response.json())
        .then(data => {
            document.getElementById('upcoming-flights').textContent = data.length;
        })
        .catch(error => {
            console.error('Error loading upcoming flights:', error);
            document.getElementById('upcoming-flights').textContent = 'Error';
        });
    
    // Load total passengers count
    fetch('php/api/passenger/read.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('total-passengers').textContent = data.length;
        })
        .catch(error => {
            console.error('Error loading passengers:', error);
            document.getElementById('total-passengers').textContent = 'Error';
        });
    
    // Load active bookings count
    fetch('php/api/booking/read.php?status=Paid')
        .then(response => response.json())
        .then(data => {
            document.getElementById('active-bookings').textContent = data.length;
        })
        .catch(error => {
            console.error('Error loading bookings:', error);
            document.getElementById('active-bookings').textContent = 'Error';
        });
    
    // Load total airlines count
    fetch('php/api/airline/read.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('total-airlines').textContent = data.length;
        })
        .catch(error => {
            console.error('Error loading airlines:', error);
            document.getElementById('total-airlines').textContent = 'Error';
        });
}

function loadRecentFlights() {
    const today = new Date();
    const nextWeek = new Date();
    nextWeek.setDate(today.getDate() + 7);
    
    const todayStr = today.toISOString().split('T')[0];
    const nextWeekStr = nextWeek.toISOString().split('T')[0];
    
    fetch(`php/api/flight/read.php?date_from=${todayStr}&date_to=${nextWeekStr}`)
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector('#recent-flights-table tbody');
            tableBody.innerHTML = '';
            
            // Sort by departure time (latest first)
            data.sort((a, b) => new Date(b.DepartureDateTime) - new Date(a.DepartureDateTime));
            
            // Show only the next 5 flights
            const recentFlights = data.slice(0, 5);
            
            if (recentFlights.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = `<td colspan="5" class="no-data">No upcoming flights</td>`;
                tableBody.appendChild(row);
                return;
            }
            
            recentFlights.forEach(flight => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${flight.FlightNumber}</td>
                    <td>${flight.AirlineCode}</td>
                    <td>${formatDateTime(flight.DepartureDateTime)}</td>
                    <td>${formatDateTime(flight.ArrivalDateTime)}</td>
                    <td><span class="status-badge ${getStatusClass(flight.Status)}">${translateStatus(flight.Status)}</span></td>
                `;
                tableBody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error loading recent flights:', error);
            document.querySelector('#recent-flights-table tbody').innerHTML = `
                <tr><td colspan="5" class="error">Error loading flights</td></tr>
            `;
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


// Import the functions you need from the SDKs you need
import { initializeApp } from "firebase/app";
import { getAnalytics } from "firebase/analytics";
// TODO: Add SDKs for Firebase products that you want to use
// https://firebase.google.com/docs/web/setup#available-libraries

// Your web app's Firebase configuration
// For Firebase JS SDK v7.20.0 and later, measurementId is optional
const firebaseConfig = {
  apiKey: "AIzaSyDMH81gcwLnrceesnKPzJAkANW25GXVp_s",
  authDomain: "airport-ec3da.firebaseapp.com",
  projectId: "airport-ec3da",
  storageBucket: "airport-ec3da.firebasestorage.app",
  messagingSenderId: "528704992711",
  appId: "1:528704992711:web:088564136119349ddd15e2",
  measurementId: "G-HWQC793Z4D"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);