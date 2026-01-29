/**
 * Main JavaScript for RLI Material Request Form
 * Handles dynamic table rows, calculations, and form submission
 */

document.addEventListener('DOMContentLoaded', function() {
    let rowCounter = 1;
    const tableBody = document.getElementById('itemsTableBody');
    const addRowBtn = document.getElementById('addRowBtn');
    const form = document.getElementById('materialRequestForm');

    // Set today's date as default for date_requested
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('date_requested').value = today;

    // Supervisor dropdown - auto-populate email and mobile
    const supervisorSelect = document.getElementById('supervisor_id');
    const supervisorEmail = document.getElementById('supervisor_email');
    const supervisorMobile = document.getElementById('supervisor_mobile');
    
    async function syncSupervisorContact() {
        if (!supervisorSelect) return;
        const id = supervisorSelect.value;

        if (supervisorEmail) supervisorEmail.value = '';
        if (supervisorMobile) supervisorMobile.value = '';

        if (!id) return;

        try {
            const res = await fetch(`get_supervisor.php?id=${encodeURIComponent(id)}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data && data.success) {
                if (supervisorEmail) supervisorEmail.value = data.email || '';
                if (supervisorMobile) supervisorMobile.value = data.mobile || '';
            }
        } catch (e) {
            // silent fail - user can still submit request
            if (supervisorEmail) supervisorEmail.value = supervisorEmail.value || '';
            if (supervisorMobile) supervisorMobile.value = supervisorMobile.value || '';
        }
    }

    if (supervisorSelect) {
        supervisorSelect.addEventListener('change', syncSupervisorContact);
        // Populate on page load (in case a value is preselected)
        syncSupervisorContact();
    }

    // Add new row to table
    function addTableRow() {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="item-no py-3 px-4 text-slate-600 font-semibold">${rowCounter}</td>
            <td class="py-3 px-4"><input type="text" name="items[${rowCounter}][item_name]" class="item-name w-full rounded-xl border border-slate-200 px-3 py-2 focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500" required></td>
            <td class="py-3 px-4"><textarea name="items[${rowCounter}][specs]" class="item-specs w-full rounded-xl border border-slate-200 px-3 py-2 focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500" rows="2"></textarea></td>
            <td class="py-3 px-4"><input type="number" name="items[${rowCounter}][quantity]" class="item-quantity w-full rounded-xl border border-slate-200 px-3 py-2 focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500" step="0.01" min="0" required></td>
            <td class="py-3 px-4"><input type="text" name="items[${rowCounter}][unit]" class="item-unit w-full rounded-xl border border-slate-200 px-3 py-2 focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500"></td>
            <td class="py-3 px-4"><input type="number" name="items[${rowCounter}][price]" class="item-price w-full rounded-xl border border-slate-200 px-3 py-2 focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500" step="0.01" min="0" required></td>
            <td class="py-3 px-4"><input type="text" name="items[${rowCounter}][amount]" class="item-amount w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700 font-semibold" readonly></td>
            <td class="py-3 px-4"><input type="url" name="items[${rowCounter}][item_link]" class="item-link w-full rounded-xl border border-slate-200 px-3 py-2 focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500" placeholder="https://..."></td>
            <td class="py-3 px-4 text-right"><button type="button" class="px-3 py-2 rounded-xl bg-red-600 text-white font-semibold hover:opacity-95" onclick="removeRow(this)">Remove</button></td>
        `;
        tableBody.appendChild(row);
        rowCounter++;

        // Attach event listeners to new row
        attachCalculationListeners(row);
    }

    // Remove row from table
    function removeRow(button) {
        const row = button.closest('tr');
        row.remove();
        updateRowNumbers();
    }

    // Update row numbers after removal
    function updateRowNumbers() {
        const rows = tableBody.querySelectorAll('tr');
        rows.forEach((row, index) => {
            row.querySelector('.item-no').textContent = index + 1;
            // Update name attributes
            const inputs = row.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                if (input.name) {
                    input.name = input.name.replace(/items\[\d+\]/, `items[${index + 1}]`);
                }
            });
        });
        rowCounter = rows.length + 1;
    }

    // Calculate amount (quantity × price)
    function calculateAmount(row) {
        const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        const amount = quantity * price;
        row.querySelector('.item-amount').value = amount.toFixed(2);
    }

    // Attach calculation listeners to a row
    function attachCalculationListeners(row) {
        const quantityInput = row.querySelector('.item-quantity');
        const priceInput = row.querySelector('.item-price');

        quantityInput.addEventListener('input', () => calculateAmount(row));
        priceInput.addEventListener('input', () => calculateAmount(row));
    }

    // Add row button event listener
    addRowBtn.addEventListener('click', addTableRow);

    // Show message function
    function showMessage(message, isSuccess) {
        // Remove existing messages
        const existingMsg = document.querySelector('.message');
        if (existingMsg) {
            existingMsg.remove();
        }

        // Create message element
        const msgDiv = document.createElement('div');
        msgDiv.className = `message ${isSuccess ? 'success' : 'error'}`;
        msgDiv.textContent = message;
        
        // Insert near top of page content (works with/without <header>)
        const target =
            document.querySelector('.bg-white.rounded-card') ||
            document.querySelector('main') ||
            document.body;
        if (target === document.body) {
            document.body.prepend(msgDiv);
        } else {
            target.prepend(msgDiv);
        }

        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });

        // Remove message after 5 seconds
        setTimeout(() => {
            msgDiv.remove();
        }, 5000);
    }

    // Success popup modal
    function showSuccessPopup(message) {
        // Remove existing popup if any
        const existing = document.getElementById('rliSuccessModal');
        if (existing) existing.remove();

        const modal = document.createElement('div');
        modal.id = 'rliSuccessModal';
        modal.className = 'fixed inset-0 z-[9999] flex items-center justify-center p-4';
        modal.innerHTML = `
          <div class="absolute inset-0 bg-black/40"></div>
          <div class="relative w-full max-w-md rounded-2xl bg-white border border-slate-100 shadow-soft p-6">
            <div class="flex items-start justify-between gap-4">
              <div>
                <div class="text-sm font-semibold text-slate-500 uppercase">Success</div>
                <div class="mt-1 text-xl font-bold text-slate-900">Request Submitted</div>
              </div>
              <button type="button" id="rliSuccessClose" class="w-10 h-10 rounded-xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold">✕</button>
            </div>
            <div class="mt-4 text-slate-700">${message}</div>
            <div class="mt-6 flex gap-3 justify-end">
              <a href="requests.php" class="px-4 py-3 rounded-2xl bg-bgGrey hover:bg-slate-200 text-slate-900 font-semibold">View Requests</a>
              <button type="button" id="rliSuccessOk" class="px-4 py-3 rounded-2xl bg-accentYellow text-black font-semibold hover:opacity-95">OK</button>
            </div>
          </div>
        `;

        document.body.appendChild(modal);

        function close() {
            modal.remove();
        }

        modal.querySelector('#rliSuccessClose')?.addEventListener('click', close);
        modal.querySelector('#rliSuccessOk')?.addEventListener('click', close);
        modal.addEventListener('click', (e) => {
            if (e.target === modal.firstElementChild) close();
        });
        window.addEventListener('keydown', function onKey(e) {
            if (e.key === 'Escape') {
                close();
                window.removeEventListener('keydown', onKey);
            }
        });
    }

    // Form validation and AJAX submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const rows = tableBody.querySelectorAll('tr');

        // Require supervisor selection
        if (supervisorSelect && !supervisorSelect.value) {
            showMessage('Please select a supervisor before submitting the request.', false);
            supervisorSelect.focus();
            return false;
        }
        
        if (rows.length === 0) {
            showMessage('Please add at least one item to the request.', false);
            return false;
        }

        // Validate each row
        let isValid = true;
        rows.forEach(row => {
            const itemName = row.querySelector('.item-name').value.trim();
            const quantity = row.querySelector('.item-quantity').value;
            const price = row.querySelector('.item-price').value;

            if (!itemName || !quantity || !price) {
                isValid = false;
            }
        });

        if (!isValid) {
            showMessage('Please fill in all required fields (Item Name, Quantity, and Price) for all items.', false);
            return false;
        }

        // Disable submit button
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';

        // Prepare form data
        const formData = new FormData(form);

        // Submit via AJAX
        fetch('save_request.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // Check if response is JSON
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.includes("application/json")) {
                return response.json();
            } else {
                // If not JSON, get text and show error
                return response.text().then(text => {
                    throw new Error('Server returned non-JSON response: ' + text.substring(0, 200));
                });
            }
        })
        .then(data => {
            if (data.success) {
                showSuccessPopup(`${data.message}<br><span class="font-semibold">Request ID:</span> ${data.request_id}`);
                // Reset form after successful submission
                setTimeout(() => {
                    form.reset();
                    // Clear table and add one empty row
                    tableBody.innerHTML = '';
                    rowCounter = 1;
                    addTableRow();
                    // Reset date_requested to today
                    document.getElementById('date_requested').value = today;
                }, 2000);
            } else {
                showMessage(data.message || 'An error occurred. Please try again.', false);
            }
        })
        .catch(error => {
            console.error('Submission Error:', error);
            showMessage('Error: ' + (error.message || 'Network error. Please check your connection and try again.'), false);
        })
        .finally(() => {
            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit Request';
        });
    });

    // Add initial row on page load
    addTableRow();
});

// Global function for remove button (accessible from inline onclick)
function removeRow(button) {
    const row = button.closest('tr');
    const tableBody = document.getElementById('itemsTableBody');
    
    if (tableBody.querySelectorAll('tr').length > 1) {
        row.remove();
        // Update row numbers
        const rows = tableBody.querySelectorAll('tr');
        rows.forEach((row, index) => {
            row.querySelector('.item-no').textContent = index + 1;
            const inputs = row.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                if (input.name) {
                    input.name = input.name.replace(/items\[\d+\]/, `items[${index + 1}]`);
                }
            });
        });
    } else {
        alert('At least one row must remain in the table.');
    }
}
