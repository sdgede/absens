export function debounce(func, delay) {
    let timeOutId;

    return function (...args) {
        clearTimeout(timeOutId);
        timeOutId = setTimeout(() => {
            func.apply(this, args);
        }, delay);
    };
}