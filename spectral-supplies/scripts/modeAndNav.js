let lightMode = false; // Global variable for light mode

window.onload = function () {
    const body = document.querySelector("body");
    const html = document.querySelector("html");
    const navToggle = document.getElementById("nav-toggle");
    const modeToggle = document.getElementById("light-mode-toggle");
    const nav = document.querySelector("nav");

    // Initialize light mode from localStorage
    checkLightMode();

    // Initialize event listeners
    initEventListeners();

    // Event Listeners
    function initEventListeners() {
        navToggle.addEventListener("click", toggleNav);
        modeToggle.addEventListener("click", toggleLightMode);
        setupSwipeHandlers();
    }

    // Light Mode Toggle
    function toggleLightMode() {
        body.classList.toggle("light-mode");
        html.classList.toggle("light-mode");
        modeToggle.src = body.classList.contains("light-mode") ? "img/moon.png" : "img/sun.png";

        lightMode = !lightMode; // Update global variable
        localStorage.setItem("lightMode", lightMode); // Persist state
    }

    function checkLightMode() {
        // Load light mode state from localStorage
        lightMode = JSON.parse(localStorage.getItem("lightMode")) || false;

        // Apply light mode if previously enabled
        if (lightMode) {
            body.classList.add("light-mode");
            html.classList.add("light-mode");
            modeToggle.src = "img/moon.png";
        }
    }

    // Nav Toggle
    function toggleNav() {
        nav.classList.toggle("show-nav");
        navToggle.src = nav.classList.contains("show-nav") ? "img/close.png" : "img/menu.png";
    }

    // Swipe functions
    function setupSwipeHandlers() {
        const hammer = new Hammer(body);
        hammer.on('swipeleft', handleSwipeLeft);
        hammer.on('swiperight', handleSwipeRight);
    }

    function handleSwipeLeft() {
        if (nav.classList.contains("show-nav")) {
            toggleNav();
        }
    }

    function handleSwipeRight() {
        if (!nav.classList.contains("show-nav")) {
            toggleNav();
        }
    }
};
