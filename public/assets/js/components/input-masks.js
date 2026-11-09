function digits(value) {
    return String(value).replace(/\D/g, '');
}

function maskCnpj(value) {
    const valueDigits = digits(value).slice(0, 14);
    let result = valueDigits.slice(0, 2);

    if (valueDigits.length > 2) {
        result += '.' + valueDigits.slice(2, 5);
    }

    if (valueDigits.length > 5) {
        result += '.' + valueDigits.slice(5, 8);
    }

    if (valueDigits.length > 8) {
        result += '/' + valueDigits.slice(8, 12);
    }

    if (valueDigits.length > 12) {
        result += '-' + valueDigits.slice(12, 14);
    }

    return result;
}

function maskPhone(value) {
    const valueDigits = digits(value).slice(0, 11);
    const area = valueDigits.slice(0, 2);
    const number = valueDigits.slice(2);

    if (valueDigits.length <= 2) {
        return area;
    }

    if (number.length <= 4) {
        return `(${area}) ${number}`;
    }

    const splitAt = number.length > 8 ? 5 : 4;

    return `(${area}) ${number.slice(0, splitAt)}-${number.slice(splitAt)}`;
}

function maskCep(value) {
    const valueDigits = digits(value).slice(0, 8);

    if (valueDigits.length <= 5) {
        return valueDigits;
    }

    return `${valueDigits.slice(0, 5)}-${valueDigits.slice(5)}`;
}

function maskCurrency(value) {
    const valueDigits = digits(value).slice(0, 15);
    
    if (valueDigits === '') {
        return '';
    }
    
    const integerPart = valueDigits.slice(0, -2) || '0';
    const decimalPart = valueDigits.slice(-2).padStart(2, '0');
    
    // Formatar parte inteira com separador de milhar (ponto)
    const formattedInteger = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    
    return `${formattedInteger},${decimalPart}`;
}

function isValidCnpj(value) {
    const cnpj = digits(value);

    if (cnpj.length !== 14 || /^(\d)\1{13}$/.test(cnpj)) {
        return false;
    }

    const calculateDigit = (base, weights) => {
        const sum = weights.reduce((total, weight, index) => total + Number(base[index]) * weight, 0);
        const remainder = sum % 11;

        return remainder < 2 ? 0 : 11 - remainder;
    };

    const first = calculateDigit(cnpj.slice(0, 12), [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);
    const second = calculateDigit(cnpj.slice(0, 13), [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);

    return first === Number(cnpj[12]) && second === Number(cnpj[13]);
}

function maskValue(mask, value) {
    if (mask === 'cnpj') {
        return maskCnpj(value);
    }

    if (mask === 'phone') {
        return maskPhone(value);
    }

    if (mask === 'cep') {
        return maskCep(value);
    }

    if (mask === 'currency') {
        return maskCurrency(value);
    }

    return value;
}

async function lookupCep(input) {
    const cep = digits(input.value);

    if (cep.length !== 8 || input.dataset.lastCep === cep) {
        return;
    }

    input.dataset.lastCep = cep;

    const field = input.closest('.form__field');
    const feedback = field?.querySelector('[data-cep-feedback]');

    if (feedback) {
        feedback.textContent = 'Buscando endereço...';
        feedback.classList.remove('form__error');
    }

    try {
        const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
        const address = await response.json();

        if (address.erro) {
            if (feedback) {
                feedback.textContent = 'CEP não encontrado.';
                feedback.classList.add('form__error');
            }

            return;
        }

        const fields = {
            logradouro: address.logradouro,
            bairro: address.bairro,
            cidade: address.localidade,
            estado: address.uf,
        };

        Object.entries(fields).forEach(([id, value]) => {
            const target = document.getElementById(id);

            if (target && value) {
                target.value = value;
            }
        });

        if (feedback) {
            feedback.textContent = 'Endereço preenchido automaticamente.';
            feedback.classList.remove('form__error');
        }
    } catch (error) {
        if (feedback) {
            feedback.textContent = 'Não foi possível consultar o CEP agora.';
            feedback.classList.add('form__error');
        }
    }
}

export function initInputMasks() {
    document.querySelectorAll('[data-mask]').forEach((input) => {
        const mask = input.dataset.mask;

        input.addEventListener('input', () => {
            input.value = maskValue(mask, input.value);

            if (mask === 'cnpj') {
                input.setCustomValidity('');
            }
        });
    });

    document.querySelectorAll('[data-mask="cnpj"]').forEach((input) => {
        input.addEventListener('blur', () => {
            const cnpj = digits(input.value);

            if (cnpj !== '' && !isValidCnpj(cnpj)) {
                input.setCustomValidity('Informe um CNPJ válido.');
                input.classList.add('is-invalid');
                return;
            }

            input.setCustomValidity('');
            input.classList.remove('is-invalid');
        });
    });

    document.querySelectorAll('[data-cep]').forEach((input) => {
        input.addEventListener('blur', () => lookupCep(input));
    });
}
