const elementsExist = (
    document.getElementById('event_isPrivate_yes') &&
    document.getElementById('event_isPrivate_no') &&
    document.querySelector('input[name="event[isPrivate]"]') &&
    document.getElementById('event_isFinancialParticipation_yes') &&
    document.getElementById('event_isFinancialParticipation_no') &&
    document.querySelector('input[name="event[isFinancialParticipation]"]') &&
    document.getElementById('event_financialParticipationAmount') &&
    document.querySelectorAll('.category-toggle-button').length > 0 &&
    document.querySelector('select[name="event[category]"]')
);

if (elementsExist) {
    /// PRIVATE EVENT ///
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

    financialParticipationAmount.style.display = 'none';

    yesButtonFinancial.addEventListener('click', function(e) {
        e.preventDefault();
        setActiveFinancial('1');
    });

    noButtonFinancial.addEventListener('click', function(e) {
        e.preventDefault();
        setActiveFinancial('0');
    });

    function setActiveFinancial(value) {
        if (value === '1') {
            yesButtonFinancial.classList.add('active');
            noButtonFinancial.classList.remove('active');
            inputHiddenFinancial.value = '1';
            financialParticipationAmount.style.display = 'flex';
        } else {
            noButtonFinancial.classList.add('active');
            yesButtonFinancial.classList.remove('active');
            inputHiddenFinancial.value = '0';
            financialParticipationAmount.style.display = 'none';
        }
    }

    /// CATEGORY ///
    const categoryButtons = document.querySelectorAll('.category-toggle-button');
    const inputHiddenCategory = document.querySelector('select[name="event[category]"]');

    categoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            categoryButtons.forEach(innerButton => {
                innerButton.classList.remove('active');
            });
            button.classList.add('active');
            inputHiddenCategory.value = button.getAttribute('data-value');
        });
    });
}