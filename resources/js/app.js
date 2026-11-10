// 1. Mengimpor library Alpine.js yang baru saja diinstall di node_modules
import Alpine from 'alpinejs';

// 2. Menyimpan Alpine ke objek global 'window' browser agar bisa diakses di mana saja (termasuk DevTools)
window.Alpine = Alpine;

// 3. Menyalakan Alpine.js agar mulai memindai atribut interaktif (x-data, x-show, @click) di halaman Blade
Alpine.start();
