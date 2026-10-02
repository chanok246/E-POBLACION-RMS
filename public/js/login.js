document.addEventListener("DOMContentLoaded", function () {

    /* ============================================================
       CAROUSEL
       ============================================================ */

    const carousel = document.querySelector(".carousel");

    if (carousel) {

        const slides = Array.from(
            carousel.querySelectorAll(".slide")
        );

        const prevButton = document.querySelector(
            ".carousel-nav.prev"
        );

        const nextButton = document.querySelector(
            ".carousel-nav.next"
        );

        const indicatorButtons = Array.from(
            document.querySelectorAll(".carousel-indicators button")
        );

        let currentSlide = 0;
        let autoPlayTimer = null;

        const AUTO_PLAY_DELAY = 5000;


        /* --------------------------------------------------------
           SHOW SLIDE
           -------------------------------------------------------- */

        function showSlide(index) {

            if (slides.length === 0) {
                return;
            }

            // Keep index inside valid range
            if (index < 0) {
                index = slides.length - 1;
            }

            if (index >= slides.length) {
                index = 0;
            }

            currentSlide = index;


            /* Update slides */

            slides.forEach(function (slide, i) {

                const isActive = i === currentSlide;

                slide.classList.toggle(
                    "is-active",
                    isActive
                );

                slide.setAttribute(
                    "aria-hidden",
                    isActive ? "false" : "true"
                );

            });


            /* Update indicators */

            indicatorButtons.forEach(function (button, i) {

                const isActive = i === currentSlide;

                button.classList.toggle(
                    "is-active",
                    isActive
                );

                button.setAttribute(
                    "aria-selected",
                    isActive ? "true" : "false"
                );

            });

        }


        /* --------------------------------------------------------
           NEXT SLIDE
           -------------------------------------------------------- */

        function nextSlide() {

            showSlide(currentSlide + 1);

        }


        /* --------------------------------------------------------
           PREVIOUS SLIDE
           -------------------------------------------------------- */

        function previousSlide() {

            showSlide(currentSlide - 1);

        }


        /* --------------------------------------------------------
           BUTTON EVENTS
           -------------------------------------------------------- */

        if (nextButton) {

            nextButton.addEventListener(
                "click",
                function (event) {

                    event.preventDefault();
                    event.stopPropagation();

                    nextSlide();

                    restartAutoPlay();

                }
            );

        }


        if (prevButton) {

            prevButton.addEventListener(
                "click",
                function (event) {

                    event.preventDefault();
                    event.stopPropagation();

                    previousSlide();

                    restartAutoPlay();

                }
            );

        }


        /* --------------------------------------------------------
           INDICATOR EVENTS
           -------------------------------------------------------- */

        indicatorButtons.forEach(function (button, index) {

            button.addEventListener(
                "click",
                function (event) {

                    event.preventDefault();
                    event.stopPropagation();

                    showSlide(index);

                    restartAutoPlay();

                }
            );

        });


        /* --------------------------------------------------------
           AUTO PLAY
           -------------------------------------------------------- */

        function startAutoPlay() {

            stopAutoPlay();

            autoPlayTimer = setInterval(
                function () {

                    nextSlide();

                },
                AUTO_PLAY_DELAY
            );

        }


        function stopAutoPlay() {

            if (autoPlayTimer !== null) {

                clearInterval(autoPlayTimer);

                autoPlayTimer = null;

            }

        }


        function restartAutoPlay() {

            startAutoPlay();

        }


        /* --------------------------------------------------------
           PAUSE WHEN MOUSE IS OVER CAROUSEL
           -------------------------------------------------------- */

        carousel.addEventListener(
            "mouseenter",
            function () {

                stopAutoPlay();

            }
        );


        carousel.addEventListener(
            "mouseleave",
            function () {

                startAutoPlay();

            }
        );


        /* --------------------------------------------------------
           KEYBOARD NAVIGATION
           -------------------------------------------------------- */

        document.addEventListener(
            "keydown",
            function (event) {

                // Don't interfere with typing in forms
                const activeElement = document.activeElement;

                const isTyping =
                    activeElement &&
                    (
                        activeElement.tagName === "INPUT" ||
                        activeElement.tagName === "TEXTAREA" ||
                        activeElement.tagName === "SELECT"
                    );

                if (isTyping) {
                    return;
                }


                if (event.key === "ArrowRight") {

                    nextSlide();

                    restartAutoPlay();

                }


                if (event.key === "ArrowLeft") {

                    previousSlide();

                    restartAutoPlay();

                }

            }
        );


        /* --------------------------------------------------------
           TOUCH / SWIPE SUPPORT
           -------------------------------------------------------- */

        let touchStartX = 0;
        let touchEndX = 0;

        carousel.addEventListener(
            "touchstart",
            function (event) {

                if (
                    event.touches &&
                    event.touches.length > 0
                ) {

                    touchStartX =
                        event.touches[0].clientX;

                }

            },
            {
                passive: true
            }
        );


        carousel.addEventListener(
            "touchend",
            function (event) {

                if (
                    event.changedTouches &&
                    event.changedTouches.length > 0
                ) {

                    touchEndX =
                        event.changedTouches[0].clientX;

                    handleSwipe();

                }

            },
            {
                passive: true
            }
        );


        function handleSwipe() {

            const difference =
                touchStartX - touchEndX;

            const minimumSwipeDistance = 50;


            if (
                Math.abs(difference) <
                minimumSwipeDistance
            ) {

                return;

            }


            if (difference > 0) {

                // Swipe left
                nextSlide();

            } else {

                // Swipe right
                previousSlide();

            }


            restartAutoPlay();

        }


        /* --------------------------------------------------------
           INITIALIZE CAROUSEL
           -------------------------------------------------------- */

        showSlide(0);

        startAutoPlay();

    }



    /* ============================================================
       SCROLL TO LOGIN
       ============================================================ */

    const scrollIndicator =
        document.getElementById("scrollIndicator");

    const loginSection =
        document.getElementById("login");


    if (
        scrollIndicator &&
        loginSection
    ) {

        scrollIndicator.addEventListener(
            "click",
            function () {

                loginSection.scrollIntoView({
                    behavior: "smooth",
                    block: "start"
                });

            }
        );

    }



    /* ============================================================
       PASSWORD SHOW / HIDE
       ============================================================ */

    const passwordInput =
        document.getElementById("password");

    const togglePassword =
        document.getElementById("togglePassword");

    const togglePasswordIcon =
        document.getElementById("togglePasswordIcon");


    if (
        passwordInput &&
        togglePassword &&
        togglePasswordIcon
    ) {

        togglePassword.addEventListener(
            "click",
            function () {

                if (
                    passwordInput.type === "password"
                ) {

                    passwordInput.type = "text";

                    togglePasswordIcon.classList.remove(
                        "bi-eye"
                    );

                    togglePasswordIcon.classList.add(
                        "bi-eye-slash"
                    );

                    togglePassword.setAttribute(
                        "aria-label",
                        "Hide password"
                    );

                } else {

                    passwordInput.type = "password";

                    togglePasswordIcon.classList.remove(
                        "bi-eye-slash"
                    );

                    togglePasswordIcon.classList.add(
                        "bi-eye"
                    );

                    togglePassword.setAttribute(
                        "aria-label",
                        "Show password"
                    );

                }

            }
        );

    }



    /* ============================================================
       ROLE SELECTOR
       ============================================================ */

    const roleButtons =
        document.querySelectorAll(".role-pill");

    const roleInput =
        document.getElementById("role");


    if (roleButtons.length > 0) {

        roleButtons.forEach(
            function (button) {

                button.addEventListener(
                    "click",
                    function () {

                        /* Remove active state */

                        roleButtons.forEach(
                            function (item) {

                                item.classList.remove(
                                    "is-active"
                                );

                                item.setAttribute(
                                    "aria-checked",
                                    "false"
                                );

                            }
                        );


                        /* Activate clicked role */

                        button.classList.add(
                            "is-active"
                        );

                        button.setAttribute(
                            "aria-checked",
                            "true"
                        );


                        /* Update hidden input */

                        if (roleInput) {

                            roleInput.value =
                                button.dataset.role;

                        }

                    }
                );

            }
        );

    }



    /* ============================================================
       SCROLL TO TOP
       ============================================================ */

    const scrollTopButton =
        document.getElementById("scrollTop");


    if (scrollTopButton) {

        function updateScrollTopButton() {

            if (window.scrollY > 500) {

                scrollTopButton.classList.add(
                    "is-visible"
                );

            } else {

                scrollTopButton.classList.remove(
                    "is-visible"
                );

            }

        }


        window.addEventListener(
            "scroll",
            updateScrollTopButton,
            {
                passive: true
            }
        );


        scrollTopButton.addEventListener(
            "click",
            function () {

                window.scrollTo({
                    top: 0,
                    behavior: "smooth"
                });

            }
        );


        updateScrollTopButton();

    }



    /* ============================================================
       REVEAL ANIMATIONS
       ============================================================ */

    const revealElements =
        document.querySelectorAll(".reveal");


    if (revealElements.length > 0) {

        if (
            "IntersectionObserver"
            in window
        ) {

            const revealObserver =
                new IntersectionObserver(
                    function (entries) {

                        entries.forEach(
                            function (entry) {

                                if (
                                    entry.isIntersecting
                                ) {

                                    entry.target.classList.add(
                                        "is-visible"
                                    );

                                    revealObserver.unobserve(
                                        entry.target
                                    );

                                }

                            }
                        );

                    },
                    {
                        threshold: 0.12
                    }
                );


            revealElements.forEach(
                function (element) {

                    revealObserver.observe(
                        element
                    );

                }
            );

        } else {

            /* Fallback for old browsers */

            revealElements.forEach(
                function (element) {

                    element.classList.add(
                        "is-visible"
                    );

                }
            );

        }

    }

});
