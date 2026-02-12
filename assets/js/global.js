/**
 * Global JavaScript Functions
 * Native Starterkit
 */

// Logout Confirmation with SweetAlert2
function confirmLogout() {
    // Check if Swal is available
    if (typeof Swal === 'undefined') {
        // Fallback if SweetAlert2 not loaded
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = 'core/logout.php';
        }
        return;
    }
    
    Swal.fire({
        title: 'Logout',
        text: 'Are you sure you want to logout?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, logout',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'core/logout.php';
        }
    });
}

// Generic Delete Confirmation
function confirmDeleteGeneric(url, id, idParamName = 'id', message = "You won't be able to revert this!") {
    Swal.fire({
        title: 'Are you sure?',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            var data = {};
            data[idParamName] = id;
            
            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                success: function(response) {
                    location.reload();
                },
                error: function(xhr, status, error) {
                    Swal.fire('Error!', 'Failed to delete.', 'error');
                    console.error(xhr.responseText);
                }
            });
        }
    });
}
