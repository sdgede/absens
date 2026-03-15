import buildRoute from "../../utils/buildRoute";
import initSearchHandler from "./initSearchHandler";

const feedbacksList = document.getElementById("feedbacksList");
const paginationContainer = document.getElementById("pagination-container");
const searchInput = document.getElementById("search");

searchInput.addEventListener("input", function (e) {
    initSearchHandler({
        searchValue: e.target.value.trim(),
        apiPath: "/admin/feedback",
        renderFn: renderFeedbackList,
    });
});

function renderFeedbackList(data) {
    feedbacksList.innerHTML = "";
    paginationContainer.innerHTML = "";

    if (data.data.length === 0) {
        feedbacksList.innerHTML = `
            <div class="bg-primary w-full flex justify-center items-center rounded-sm py-4 px-4">
                <h1 class="text-gray-300 text-lg md:text-xl text-center font-semibold">
                    No data found
                </h1>
            </div>
        `;

        return;
    }

    data.data.forEach((item) => {
        feedbacksList.innerHTML += `
            <div class="bg-primary w-full flex justify-between items-center rounded-sm py-2 px-4">
                <div class="flex items-center gap-3">
                    <img src="/img/profile-img.png" alt="profile" class="w-7 h-7 md:w-15 md:h-15">
                    <div class="flex flex-col justify-center">
                        <p class="text-white text-[.9rem] md:ext-lg font-bold">${item?.user?.name}</p>
                        <p class="text-white text-[.8rem] md:ext-lg">${item?.comment}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex gap-1">
                        ${Array.from({length : item?.star_rating}).map(() => `
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"
                                class="w-3 h-3 md:w-5 md:h-5 text-yellow-500 cursor-pointer">
                                <path fill="currentColor"
                                    d="M316.9 18C311.6 7 300.4 0 288.1 0s-23.4 7-28.8 18L195 150.3 51.4 171.5c-12 1.8-22 10.2-25.7 21.7s-.7 24.2 7.9 32.7L137.8 329 113.2 474.7c-2 12 3 24.2 12.9 31.3s23 8 33.8 2.3l128.3-68.5 128.3 68.5c10.8 5.7 23.9 4.9 33.8-2.3s14.9-19.3 12.9-31.3L438.5 329 542.7 225.9c8.6-8.5 11.7-21.2 7.9-32.7s-13.7-19.9-25.7-21.7L381.2 150.3 316.9 18z" />
                            </svg>
                        `).join('')}
                    </div>

                    <div class="flex items-center gap-2">
                        <button 
                            data-modal-target="modalInformationFeedback" 
                            data-modal-key="showModalInfoFeedback"
                            data-id="${item?.id}"
                            class="bg-white cursor-pointer rounded-sm text-primary p-1 md:p-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 md:w-5 md:h-5"
                                fill="currentColor" viewBox="0 0 512 512">
                                <path
                                    d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zm0-384c13.3 0 24 10.7 24 24l0 112c0 13.3-10.7 24-24 24s-24-10.7-24-24l0-112c0-13.3 10.7-24 24-24zM224 352a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z" />
                            </svg>
                        </button>
                        <form action="${buildRoute('feedback_admin_delete', item?.id)}" method="POST"
                            onsubmit="return confirm('Are you sure to want delete this?')">
                            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button class="p-2 rounded bg-red-600 text-white hover:bg-red-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor"
                                    viewBox="0 0 448 512">
                                    <path
                                        d="M135.2 17.7C140.6 7.1 151.3 0 163.2 0h121.6c11.9 0 22.6 7.1 28 17.7L328 32h88c13.3 0 24 10.7 24 24s-10.7 24-24 24h-16l-21.2 339.3c-1.6 25.5-22.9 45.7-48.5 45.7H117.7c-25.6 0-46.9-20.2-48.5-45.7L48 80H32c-13.3 0-24-10.7-24-24S18.7 32 32 32h88l15.2-14.3zM182.6 160c-6.6 0-12 5.4-12 12v208c0 6.6 5.4 12 12 12s12-5.4 12-12V172c0-6.6-5.4-12-12-12zm82.8 0c-6.6 0-12 5.4-12 12v208c0 6.6 5.4 12 12 12s12-5.4 12-12V172c0-6.6-5.4-12-12-12z" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        `;
    });

    paginationContainer.innerHTML = data.pagination;
}
