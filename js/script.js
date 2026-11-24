// js/script.js - vanilla JavaScript for Lagoon Breeze Villa

// Return today's date as YYYY-MM-DD (local time)
function todayString() {
    var d = new Date();
    var month = String(d.getMonth() + 1).padStart(2, '0');
    var day = String(d.getDate()).padStart(2, '0');
    return d.getFullYear() + '-' + month + '-' + day;
}

// Return the date one day after the given YYYY-MM-DD string
function nextDay(dateStr) {
    var d = new Date(dateStr + 'T00:00:00');
    d.setDate(d.getDate() + 1);
    var month = String(d.getMonth() + 1).padStart(2, '0');
    var day = String(d.getDate()).padStart(2, '0');
    return d.getFullYear() + '-' + month + '-' + day;
}

// Number of nights between two date strings
function nightsBetween(inStr, outStr) {
    var a = new Date(inStr + 'T00:00:00');
    var b = new Date(outStr + 'T00:00:00');
    return Math.round((b - a) / (1000 * 60 * 60 * 24));
}

// Mobile menu button
var menuToggle = document.getElementById('menuToggle');
if (menuToggle) {
    menuToggle.addEventListener('click', function () {
        document.getElementById('mainNav').classList.toggle('open');
    });
}

// Date rules shared by the search bars and the booking form
function setupDateFields(checkIn, checkOut) {
    var today = todayString();
    checkIn.min = today;
    checkOut.min = today;

    function fixCheckoutMin() {
        if (checkIn.value) {
            checkOut.min = nextDay(checkIn.value);
            if (checkOut.value && checkOut.value <= checkIn.value) {
                checkOut.value = '';
            }
        }
    }
    checkIn.addEventListener('change', fixCheckoutMin);
    fixCheckoutMin();
}

// Search bars (home page + rooms page): both dates or none, check-out after check-in
var searchForms = document.querySelectorAll('.date-search');
for (var i = 0; i < searchForms.length; i++) {
    (function (form) {
        var cin = form.querySelector('[name=check_in]');
        var cout = form.querySelector('[name=check_out]');
        setupDateFields(cin, cout);
        form.addEventListener('submit', function (ev) {
            if ((cin.value && !cout.value) || (!cin.value && cout.value)) {
                alert('Please choose both a check-in and a check-out date.');
                ev.preventDefault();
            } else if (cin.value && cin.value < todayString()) {
                alert('Check-in date cannot be in the past.');
                ev.preventDefault();
            } else if (cin.value && cout.value <= cin.value) {
                alert('Check-out must be after check-in.');
                ev.preventDefault();
            }
        });
    })(searchForms[i]);
}

// Booking form: validation + live price calculation
var bookingForm = document.getElementById('booking-form');
if (bookingForm) {
    var roomSelect = document.getElementById('room_id');
    var inField = document.getElementById('check_in');
    var outField = document.getElementById('check_out');
    var guestField = document.getElementById('guests');
    var errorBox = document.getElementById('js-errors');
    var nightsText = document.getElementById('sum-nights');
    var totalText = document.getElementById('sum-total');
    var maxText = document.getElementById('sum-max');

    setupDateFields(inField, outField);

    // Collect problems with the current form values
    function getErrors() {
        var errors = [];
        var opt = roomSelect.options[roomSelect.selectedIndex];
        var maxGuests = parseInt(opt.getAttribute('data-max')) || 0;
        var guests = parseInt(guestField.value) || 0;

        if (!roomSelect.value) { errors.push('Please select a room.'); }
        if (!inField.value) { errors.push('Please choose a check-in date.'); }
        else if (inField.value < todayString()) { errors.push('Check-in date cannot be in the past.'); }
        if (!outField.value) { errors.push('Please choose a check-out date.'); }
        else if (inField.value && outField.value <= inField.value) { errors.push('Check-out must be after check-in.'); }
        if (guests < 1) { errors.push('At least 1 guest is required.'); }
        else if (maxGuests && guests > maxGuests) { errors.push('This room allows a maximum of ' + maxGuests + ' guests.'); }
        return errors;
    }

    // Update nights, total price and max guests using DOM manipulation
    function updateSummary() {
        var opt = roomSelect.options[roomSelect.selectedIndex];
        var price = parseFloat(opt.getAttribute('data-price')) || 0;
        var maxGuests = parseInt(opt.getAttribute('data-max')) || 0;

        if (maxGuests) {
            guestField.max = maxGuests;
            maxText.textContent = maxGuests;
        } else {
            maxText.textContent = '-';
        }

        if (inField.value && outField.value && outField.value > inField.value && price > 0) {
            var nights = nightsBetween(inField.value, outField.value);
            nightsText.textContent = nights;
            totalText.textContent = 'LKR ' + (nights * price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        } else {
            nightsText.textContent = '0';
            totalText.textContent = 'LKR 0.00';
        }

        var errors = getErrors();
        errorBox.innerHTML = '';
        if (errors.length > 0 && (inField.value || outField.value)) {
            var ul = document.createElement('ul');
            for (var k = 0; k < errors.length; k++) {
                var li = document.createElement('li');
                li.textContent = errors[k];
                ul.appendChild(li);
            }
            errorBox.appendChild(ul);
            errorBox.style.display = 'block';
        } else {
            errorBox.style.display = 'none';
        }
    }

    roomSelect.addEventListener('change', updateSummary);
    inField.addEventListener('change', updateSummary);
    outField.addEventListener('change', updateSummary);
    guestField.addEventListener('input', updateSummary);

    bookingForm.addEventListener('submit', function (ev) {
        var errors = getErrors();
        if (errors.length > 0) {
            ev.preventDefault();
            alert(errors.join('\n'));
        }
    });
    updateSummary();
}

// Confirm before dangerous actions (buttons with class "confirm-action")
var confirmButtons = document.querySelectorAll('.confirm-action');
for (var c = 0; c < confirmButtons.length; c++) {
    confirmButtons[c].addEventListener('click', function (ev) {
        if (!confirm(this.getAttribute('data-message') || 'Are you sure?')) {
            ev.preventDefault();
        }
    });
}
