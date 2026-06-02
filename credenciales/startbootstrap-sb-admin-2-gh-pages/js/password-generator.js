$(document).ready(function() {
    $(document).on('click', '.btn-generar-password', function() {
        var input = $(this).closest('.input-group').find('.password-input');
        if (!input.length) {
            input = $(this).closest('.form-group').find('.password-input');
        }
        if (!input.length) {
            input = $('#' + $(this).data('target'));
        }
        var length = parseInt($(this).data('length')) || 16;
        var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+-=[]{}|;:,.<>?';
        var password = '';
        var hasUpper = false, hasLower = false, hasNumber = false, hasSymbol = false;

        while (!(hasUpper && hasLower && hasNumber && hasSymbol) || password.length < length) {
            var char = chars.charAt(Math.floor(Math.random() * chars.length));
            password += char;
            if (char >= 'A' && char <= 'Z') hasUpper = true;
            else if (char >= 'a' && char <= 'z') hasLower = true;
            else if (char >= '0' && char <= '9') hasNumber = true;
            else hasSymbol = true;
            if (password.length > length * 2) break;
        }

        input.val(password.substring(0, length));
        var btn = $(this);
        var original = btn.html();
        btn.html('<i class="fas fa-check"></i>');
        setTimeout(function() {
            btn.html(original);
        }, 1000);
    });
});