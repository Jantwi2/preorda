// Get DOM elements
const checkoutBtn = document.getElementById('checkout-btn');
const paymentModal = document.getElementById('payment-modal');
const confirmBtn = document.getElementById('confirm-payment');
const cancelBtn = document.getElementById('cancel-payment');
const checkoutMessage = document.getElementById('checkout-message');
const cartContainer = document.getElementById('cart-container');
const confirmationContainer = document.getElementById('confirmation-container');

// Show payment modal
checkoutBtn?.addEventListener('click', () => {
    paymentModal.style.display = 'block';
});

// Cancel payment modal
cancelBtn?.addEventListener('click', () => {
    paymentModal.style.display = 'none';
    showMessage('Payment cancelled.', 'error');
});

// Confirm payment
confirmBtn?.addEventListener('click', () => {
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Processing...';


    // Send async request to process checkout
// ...existing code...
    // Send async request to process checkout
    fetch('../actions/process_checkout_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    })
    // first get text so we can see HTML errors while debugging
    .then(response => response.text())
    .then(text => {
        let data;
        try {
            data = JSON.parse(text);
        } catch (err) {
            console.error('Server response (non-JSON):', text);
            confirmBtn.disabled = false;
            confirmBtn.textContent = "Yes, I've Paid";
            showMessage('Server error — check developer console / PHP logs.', 'error');
            return;
        }
        // proceed with parsed JSON
        if (data.status === 'success') {
            cartContainer.style.display = 'none';
            paymentModal.style.display = 'none';
            confirmationContainer.style.display = 'block';
            document.getElementById('order-id').textContent = data.order_id;
            showMessage(data.message || '✅ Order placed successfully!', 'success');
        } else {
            showMessage(data.message || '❌ Failed to process checkout. Try again.', 'error');
        }
    })
    .catch(err => { console.error(err);
        confirmBtn.disabled = false;
        confirmBtn.textContent = "Yes, I've Paid";
        showMessage('⚠️ Something went wrong. Please try again.', 'error');
    });
});

// Function to show messages dynamically
function showMessage(msg, type = 'info') {
    if (!checkoutMessage) return;
    checkoutMessage.textContent = msg;
    checkoutMessage.className = type; // style using CSS: .success, .error, .info
    checkoutMessage.style.display = 'block';
    setTimeout(() => {
        checkoutMessage.style.display = 'none';
    }, 5000);
}

// Optional: smooth transitions between screens
function switchScreen(hideElement, showElement) {
    hideElement.style.opacity = 0;
    setTimeout(() => {
        hideElement.style.display = 'none';
        showElement.style.display = 'block';
        showElement.style.opacity = 1;
    }, 300);
}
