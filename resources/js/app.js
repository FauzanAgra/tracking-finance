// 1. Mengimpor library Alpine.js yang baru saja diinstall di node_modules
import Alpine from 'alpinejs';

// Helper format pemisah ribuan (.) dan desimal koma (,) untuk input nominal uang
window.parseMoney = function (displayValue) {
    if (displayValue === null || displayValue === undefined) {
        return '';
    }

    const str = String(displayValue);
    // Hanya izinkan digit 0-9 dan koma (,)
    const cleaned = str.replace(/[^0-9,]/g, '');
    if (cleaned === '') {
        return '';
    }

    const parts = cleaned.split(',');
    const intPart = parts[0].replace(/^0+(?=\d)/, '');
    if (parts.length === 1) {
        return intPart;
    }

    const decPart = parts.slice(1).join('');
    return (intPart === '' ? '0' : intPart) + '.' + decPart;
};

window.formatMoney = function (rawValue) {
    if (rawValue === null || rawValue === undefined || rawValue === '') {
        return '';
    }

    const str = String(rawValue);
    const hasDot = str.includes('.');
    const [rawInt, rawDec] = str.split('.');
    const digitsOnly = (rawInt || '').replace(/[^0-9]/g, '');

    if (digitsOnly === '' && !hasDot) {
        return '';
    }

    const normalizedInt = digitsOnly === '' ? '0' : digitsOnly.replace(/^0+(?=\d)/, '');
    const formattedInt = normalizedInt.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    if (hasDot) {
        return formattedInt + ',' + (rawDec ?? '').replace(/[^0-9]/g, '');
    }

    return formattedInt;
};

// 2. Menyimpan Alpine ke objek global 'window' browser agar bisa diakses di mana saja (termasuk DevTools)
window.Alpine = Alpine;

// 3. Menyalakan Alpine.js agar mulai memindai atribut interaktif (x-data, x-show, @click) di halaman Blade
Alpine.start();
