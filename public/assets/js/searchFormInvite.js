import $ from 'jquery';
import 'select2';


$(function() {
    $('.js-select2').select2({
        placeholder: "Entrez l'email de l'utilisateur à inviter",
        allowClear: true
    });
});