$(function() {
    $(".datepicker").datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
        yearRange: "-100:+0" // Par exemple pour limiter la sélection à 100 ans en arrière à partir d'aujourd'hui.
    });
});

$(function() {
    $(".datetimepicker").datetimepicker({
        format: 'Y-m-d H:i',
        step: 15, // Incrémente l'heure par tranche de 15 minutes, ajustez selon vos besoins
    });
});