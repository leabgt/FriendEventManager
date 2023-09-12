import 'select2';

document.addEventListener("DOMContentLoaded", function() {
    let userSelect = document.querySelector('.user-select');
    if (userSelect) {
        $(userSelect).select2({
            placeholder: 'Entrez l\'email de l\'utilisateur',
            allowClear: true
        });
    }
});