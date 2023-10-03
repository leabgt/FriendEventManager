// document.addEventListener('DOMContentLoaded', function() {
//     // Cachez initialement le champ financialParticipationAmount
//     var financialParticipationAmount = document.getElementById('event_financialParticipationAmount');
//     financialParticipationAmount.style.display = 'none';

//     // Lorsque la case isFinancialParticipation change de valeur
//     var isFinancialParticipation = document.getElementById('event_isFinancialParticipation');
//     isFinancialParticipation.addEventListener('change', function() {
//         if (this.checked) {
//             // Si coché, affichez le champ financialParticipationAmount
//             financialParticipationAmount.style.display = 'block';
//         } else {
//             // Sinon, masquez-le
//             financialParticipationAmount.style.display = 'none';
//         }
//     });
// });

document.addEventListener('DOMContentLoaded', function() {
    // Cachez initialement le champ financialParticipationAmount
    var financialParticipationAmount = document.getElementById('event_financialParticipationAmount');
    financialParticipationAmount.style.display = 'none';

    // Lorsque la valeur de isFinancialParticipation change
    var isFinancialParticipation = document.getElementById('event_isFinancialParticipation');
    isFinancialParticipation.addEventListener('change', function() {
        if (this.value == "1") { // vérifie si la valeur est "1"
            // Si c'est le cas, affichez le champ financialParticipationAmount
            financialParticipationAmount.style.display = 'block';
        } else {
            // Sinon, masquez-le
            financialParticipationAmount.style.display = 'none';
        }
    });
});