const currentPage = document.body.getAttribute("data-page");

if (currentPage === "landing") {
    document.addEventListener("DOMContentLoaded", function () {
        // Testimonial slider
        const testimonialSlider = document.getElementById("testimonialSlider");
        const prevTestimonial = document.getElementById("prevTestimonial");
        const nextTestimonial = document.getElementById("nextTestimonial");
        const testimonialDots = document.querySelectorAll(".testimonial-dot");

        if (testimonialSlider && prevTestimonial && nextTestimonial) {
            let currentIndex = 0;
            const totalSlides = testimonialSlider.children.length;

            function showSlide(index) {
                if (index < 0) index = totalSlides - 1;
                if (index >= totalSlides) index = 0;

                currentIndex = index;
                testimonialSlider.style.transform = `translateX(-${currentIndex * 100}%)`;

                // Update dots
                testimonialDots.forEach((dot, i) => {
                    if (i === currentIndex) {
                        dot.classList.add("opacity-100");
                        dot.classList.remove("opacity-50");
                    } else {
                        dot.classList.add("opacity-50");
                        dot.classList.remove("opacity-100");
                    }
                });
            }

            prevTestimonial.addEventListener("click", () =>
                showSlide(currentIndex - 1),
            );
            nextTestimonial.addEventListener("click", () =>
                showSlide(currentIndex + 1),
            );

            // Dot navigation
            testimonialDots.forEach((dot, i) => {
                dot.addEventListener("click", () => showSlide(i));
            });

            // Auto slide every 5 seconds
            setInterval(() => showSlide(currentIndex + 1), 5000);
        }
    });
}
