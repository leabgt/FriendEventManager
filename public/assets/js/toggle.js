/// PRIVATE EVENT ? ///
const yesButton = document.getElementById('event_isPrivate_yes');
const noButton = document.getElementById('event_isPrivate_no');
const inputHidden = document.querySelector('input[name="event[isPrivate]"]');

yesButton.addEventListener('click', function() {
    setActive('1');
});

noButton.addEventListener('click', function() {
    setActive('0');
});

function setActive(value) {
    if (value === '1') {
        yesButton.classList.add('active');
        noButton.classList.remove('active');
        inputHidden.value = '1';
    } else {
        noButton.classList.add('active');
        yesButton.classList.remove('active');
        inputHidden.value = '0';
    }
}

/// FINANCIAL PARTICIPATION ///
const yesButtonFinancial = document.getElementById('event_isFinancialParticipation_yes');
const noButtonFinancial = document.getElementById('event_isFinancialParticipation_no');
const inputHiddenFinancial = document.querySelector('input[name="event[isFinancialParticipation]"]');
const financialParticipationAmount = document.getElementById('event_financialParticipationAmount');

// Cachez initialement le champ financialParticipationAmount
financialParticipationAmount.style.display = 'none';

yesButtonFinancial.addEventListener('click', function(e) {
    e.preventDefault(); // Empêche la soumission du formulaire
    setActiveFinancial('1');
});

noButtonFinancial.addEventListener('click', function(e) {
    e.preventDefault(); // Empêche la soumission du formulaire
    setActiveFinancial('0');
});

function setActiveFinancial(value) {
    if (value === '1') {
        yesButtonFinancial.classList.add('active');
        noButtonFinancial.classList.remove('active');
        inputHiddenFinancial.value = '1';
        financialParticipationAmount.style.display = 'flex'; // Affiche le champ
    } else {
        noButtonFinancial.classList.add('active');
        yesButtonFinancial.classList.remove('active');
        inputHiddenFinancial.value = '0';
        financialParticipationAmount.style.display = 'none'; // Cache le champ
    }
}

/// CATEGORY ///

const categoryButtons = document.querySelectorAll('.category-toggle-button');
const inputHiddenCategory = document.querySelector('select[name="event[category]"]');

categoryButtons.forEach(button => {
    button.addEventListener('click', function() {
        // Désactive tous les boutons
        categoryButtons.forEach(innerButton => {
            innerButton.classList.remove('active');
        });

        // Active le bouton actuellement cliqué
        button.classList.add('active');

        // Met à jour la valeur du champ caché
        inputHiddenCategory.value = button.getAttribute('data-value');
    });
});




