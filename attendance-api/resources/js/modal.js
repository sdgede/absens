const closeIcons = document.querySelectorAll("[data-close-icon]");
// const closeButtons = document.querySelectorAll("[data-close-button]");
const closeButtons = document.querySelectorAll(".close-button");

closeIcons.forEach((button) => {
    button.addEventListener("click", () => {
        const modal = button.closest(".modal");
        closeModal(modal);
    });
});

document.addEventListener("click", (event) => {
    if (event.target.closest("[data-close-button]")) {
        event.stopPropagation();
        const button = event.target.closest("[data-close-button]");
        const modal = button.closest(".modal");
        closeModal(modal);
    }
});

/**
 * Initializes a global modal functionality by attaching a click event listener to the document.
 * The modal is triggered by elements with the `data-modal-target` attribute.
 * If the `data-fetch` attribute is not set to "false", a callback function is executed before showing the modal.
 *
 * @param {Function} callback - An asynchronous function to be executed before showing the modal.
 *                              Receives the dataset of the clicked element as its argument.
 */
// initializeModal(null, null);
const initializedModals = new Set();
export default function initializeModal(key = null, callback) {
    const uniqueKey = key || "__default__";

    if (initializedModals.has(uniqueKey)) return;
    initializedModals.add(uniqueKey);

    document.addEventListener("click", async (event) => {
        const button = event.target.closest("[data-modal-target]");
        if (!button) return;

        const modalKey = button.getAttribute("data-modal-key");
        const shouldFetch = button.dataset.fetch !== "false";

        // Allow modals with shouldFetch to bypass key and callback checks
        if (shouldFetch && key && modalKey !== key) return;

        const modalId = button.getAttribute("data-modal-target");
        const modal = document.getElementById(modalId);
        const modalContent = modal.querySelector(".modal-content");

        // Execute the callback only if shouldFetch is true and a callback is provided
        if (shouldFetch && typeof callback === "function") {
            await callback(button.dataset);
        }

        showModal(modal, modalContent);
    });
}

/**
 * Handles the display of the modal when there is a validation error in the input data.
 * Ensures the modal is shown again to allow the user to correct their input.
 **/
document.addEventListener("DOMContentLoaded", () => {
    if (window.modalToShow) {
        const modal = document.getElementById(window.modalToShow);
        const modalContent = modal.querySelector(".modal-content");

        showModal(modal, modalContent);
    }
});

/**
 * Displays the modal with animations.
 * @param {HTMLElement} modal - The modal element to display.
 * @param {HTMLElement} modalContent - The modal content element for scaling animation.
 */
export function showModal(modal, modalContent) {
    modal.classList.remove("hidden");
    setTimeout(() => {
        modal.classList.remove("opacity-0");
        modal.classList.add("opacity-100");
        modalContent.classList.remove("scale-95");
        modalContent.classList.add("scale-100");
    }, 10);

    // Close modal when clicking outside the modal content
    modal.addEventListener("click", (e) => {
        if (e.target === modal) closeModal(modal);
    });

    // Close modal when pressing the Escape key
    document.addEventListener("keydown", handleEscapeKey(modal));
}

/**
 * Handles the Escape key press to close the modal.
 * @param {HTMLElement} modal - The modal element to close.
 * @returns {Function} - The event handler function.
 */
function handleEscapeKey(modal) {
    return function (e) {
        if (e.key === "Escape" && !modal.classList.contains("hidden")) {
            closeModal(modal);
        }
    };
}

/**
 * Closes the modal with animations.
 * @param {HTMLElement} modal - The modal element to close.
 */
function closeModal(modal) {
    const modalContent = modal.querySelector(".modal-content");

    modal.classList.remove("opacity-100");
    modal.classList.add("opacity-0");
    modalContent.classList.remove("scale-100");
    modalContent.classList.add("scale-95");

    setTimeout(() => {
        modal.classList.add("hidden");
    }, 300);
}
