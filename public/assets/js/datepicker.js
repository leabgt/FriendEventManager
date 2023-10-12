import 'jquery-ui/ui/widgets/datepicker';
import 'jquery-datetimepicker';

$(function() {
    $(".datepicker").datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
        yearRange: "-100:+0" 
    });
});

$(function() {
    $(".datetimepicker").datetimepicker({
        format: 'Y-m-d H:i',
        step: 15, 
    });
});