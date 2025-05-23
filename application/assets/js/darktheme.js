loadTheme = typeof loadTheme === 'function' ? loadTheme : () => localStorage.getItem('theme');
saveTheme = typeof saveTheme === 'function' ? saveTheme : theme => localStorage.setItem('theme', theme);

const themeChangeHandlers = [];

// =============== Initialization ===============

initTheme();

const darkSwitch = document.getElementById('darkSwitch');
if (darkSwitch != null) {
    darkSwitch.checked = getTheme() === 'dark';
    darkSwitch.onchange = () => {
        setTheme(darkSwitch.checked ? 'dark' : 'light');
    };
}


themeChangeHandlers.push(theme => darkSwitch.checked = theme === 'dark');

// =============== Methods ===============

// adapted from https://github.com/coliff/dark-mode-switch

function initTheme() {
    displayTheme(getTheme());
}

function getTheme() {
    return loadTheme() || (window.matchMedia(
        '(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
}

function setTheme(theme) {
    saveTheme(theme);
    displayTheme(theme);
}

function displayTheme(theme) {
    document.body.setAttribute('data-theme', theme);
    for (let handler of themeChangeHandlers) {
        handler(theme);
    }
}