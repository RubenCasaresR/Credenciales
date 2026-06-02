$(document).ready(function() {
    var darkMode = localStorage.getItem('darkMode') === 'true';

    function applyDarkMode(enabled) {
        if (enabled) {
            $('body').addClass('dark-mode');
            $('#darkModeToggle i').removeClass('fa-moon').addClass('fa-sun');
            $('#darkModeToggle').html('<i class="fas fa-sun"></i>');
        } else {
            $('body').removeClass('dark-mode');
            $('#darkModeToggle i').removeClass('fa-sun').addClass('fa-moon');
            $('#darkModeToggle').html('<i class="fas fa-moon"></i>');
        }
        localStorage.setItem('darkMode', enabled);
    }

    applyDarkMode(darkMode);

    $(document).on('click', '#darkModeToggle', function() {
        darkMode = !darkMode;
        applyDarkMode(darkMode);
    });

    $('#darkModeToggle').removeClass('d-none');
});