$(document).ready(function() {
    $('#show-register').on('click', function(e) {
        e.preventDefault();
        $('#login-form').addClass('hidden');
        $('#register-form').removeClass('hidden');
    });

    $('#show-login').on('click', function(e) {
        e.preventDefault();
        $('#register-form').addClass('hidden');
        $('#login-form').removeClass('hidden');
    });

    $('#register').on('submit', function(e) {
        e.preventDefault();
        const data = {
            username: $('#register-username').val(),
            password: $('#register-password').val()
        };
        $.ajax({
            url: 'backend/register.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                $('#message').text(response.message).removeClass('text-red-500').addClass('text-green-500');
                if (response.success) {
                    setTimeout(() => $('#show-login').click(), 2000);
                }
            }
        });
    });

    $('#login').on('submit', function(e) {
        e.preventDefault();
        const data = {
            username: $('#login-username').val(),
            password: $('#login-password').val()
        };
        $.ajax({
            url: 'backend/login.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                if (response.success) {
                    $('#message').text(response.message).removeClass('text-red-500').addClass('text-green-500');
                    // Redirect ke halaman utama setelah berhasil login
                    window.location.href = 'index.html';
                } else {
                    $('#message').text(response.message).removeClass('text-green-500').addClass('text-red-500');
                }
            }
        });
    });
});