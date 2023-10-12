document.addEventListener('DOMContentLoaded', function() {
    var financialParticipationAmount = document.getElementById('event_financialParticipationAmount');
    var isFinancialParticipation = document.getElementById('event_isFinancialParticipation');
    
    if (financialParticipationAmount && isFinancialParticipation) {
        financialParticipationAmount.style.display = 'none';

        isFinancialParticipation.addEventListener('change', function() {
            if (this.value == "1") { 
                financialParticipationAmount.style.display = 'block';
            } else {
                financialParticipationAmount.style.display = 'none';
            }
        });
    } 
});