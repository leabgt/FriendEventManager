document.addEventListener('DOMContentLoaded', function() {
    // Cachez initialement le champ financialParticipationAmount
    var financialParticipationAmount = document.getElementById('event_financialParticipationAmount');
    financialParticipationAmount.style.display = 'none';

    // Lorsque la case isFinancialParticipation change de valeur
    var isFinancialParticipation = document.getElementById('event_isFinancialParticipation');
    isFinancialParticipation.addEventListener('change', function() {
        if (this.checked) {
            // Si coché, affichez le champ financialParticipationAmount
            financialParticipationAmount.style.display = 'block';
        } else {
            // Sinon, masquez-le
            financialParticipationAmount.style.display = 'none';
        }
    });
});