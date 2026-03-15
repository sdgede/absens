export function hideLoader() {
    const loader = document.querySelector(".loader");
    if (loader) loader.style.display = "none";
}

export function showLoader() {
    const loader = document.querySelector(".loader");
    if (loader) loader.style.display = "block";
}