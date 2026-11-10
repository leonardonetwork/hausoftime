// Haus of Time — Main JS
// All core JS is inline in layouts for simplicity.
// Add Alpine.js or other libs here if needed in future.

// CSRF token for all fetch requests
const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (token) {
    window._csrfToken = token;
}
