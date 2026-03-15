const menuToggle = document.getElementById("menuToggle");
const mobileMenu = document.getElementById("mobileMenu");
const currentPage = document.body.getAttribute("data-page");
const sections = document.querySelectorAll("section[id]");
const navLinks = document.querySelectorAll(".nav__link");
const navBile = document.querySelector(".nav__bile");

if (currentPage === "landing") {
    // Mobile menu toggle
    if (menuToggle && mobileMenu) {
        menuToggle.addEventListener("click", function () {
            mobileMenu.classList.toggle("hidden");
        });
    }

    // Navigation Bar Highlight
    const scrollActive = () => {
        const scrollY = window.scrollY;
    
        sections.forEach((section, index) => {
            const sectionTop = section.offsetTop - 100;
            const sectionHeight = section.offsetHeight;
            const sectionId = section.getAttribute("id");
    
            // Target corresponding links
            const desktopLink = document.querySelector(`.nav-desktop a[href="#${sectionId}"]`);
            const mobileLink = document.querySelector(`.nav-mobile a[href="#${sectionId}"]`);
    
            if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                // Highlight current section link
                desktopLink?.classList.add("border-b-2", "border-white");
                mobileLink?.classList.add("bg-white/20", "font-semibold");
    
                // Optional: add background to mobile nav
                document.querySelector(".nav-mobile")?.classList.add("bg-teal/80");
            } else {
                // Remove highlight
                desktopLink?.classList.remove("border-b-2", "border-white");
                mobileLink?.classList.remove("bg-white/20", "font-semibold");
            }
        });
    };
    
    

    window.addEventListener("scroll", scrollActive);
}
