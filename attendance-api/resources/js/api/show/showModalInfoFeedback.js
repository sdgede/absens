import initializeModal from "../../modal";
import fetchDetailToModal from "./fetchDetailToModal";
import buildRoute from "../../utils/buildRoute";

initializeModal('showModalInfoFeedback', async ({ id }) => {
    await fetchDetailToModal({
        id: id,
        url: "/admin/feedback",
        renderFn: renderModalInformationFeedback,
    });
});

function renderModalInformationFeedback(response) {
    const modal = document.getElementById("modalInformationFeedback");
    const modalContent = modal.querySelector(".modal-data");
    const data = response.data;

    if (!data) return;

    modalContent.innerHTML = `
        <h2 class="text-xl text-center text-primary font-bold tracking-wide mb-4">Feedback #${data?.id}</h2>

        <div class="space-y-4">
            <div class="w-full bg-secondary p-2 px-6 text-primary">
                <p class="text-right mb-2">${data?.user?.email}</p>
                <div class="flex items-center gap-4 mb-2">
                    <div class="h-25 w-25 rounded-full bg-white flex items-center border border-primary justify-center text-primary font-medium text-5xl uppercase">
                        ${data?.user?.name.substr(0, 2)}
                    </div>
                    <div>
                        <h1 class="font-medium text-2xl">${data?.user?.name} (${data?.user?.id})</h1>
                        <p class="text-[.8rem]">${data?.user?.telp ?? 'No Telp'}</p>
                    </div>
                </div>
                <p class="text-right">${new Date(data?.user?.created_at).toLocaleDateString("id-ID", {
                    day: "2-digit",
                    month: "long",
                    year: "numeric",
                })}</p>
            </div>

            <div class="flex flex-col space-y-4">
                <label class="text-sm font-bold text-primary mb-1">Giving Comment</label>
                <div class="bg-secondary flex flex-col justify-start items-start placeholder:text-primary px-4 py-2 w-full h-32 rounded-md resize-none outline-none text-sm">
                    <div class="flex gap-1 mb-2">
                        ${Array.from({length: data?.star_rating}).map(() => `
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"
                                class="w-3 h-3 md:w-5 md:h-5 text-yellow-500 cursor-pointer">
                                <path fill="currentColor"
                                    d="M316.9 18C311.6 7 300.4 0 288.1 0s-23.4 7-28.8 18L195 150.3 51.4 171.5c-12 1.8-22 10.2-25.7 21.7s-.7 24.2 7.9 32.7L137.8 329 113.2 474.7c-2 12 3 24.2 12.9 31.3s23 8 33.8 2.3l128.3-68.5 128.3 68.5c10.8 5.7 23.9 4.9 33.8-2.3s14.9-19.3 12.9-31.3L438.5 329 542.7 225.9c8.6-8.5 11.7-21.2 7.9-32.7s-13.7-19.9-25.7-21.7L381.2 150.3 316.9 18z" />
                            </svg>
                        `).join('')}
                    </div>
                    ${data?.comment}
                </div>
            </div>

            <div class=" bg-white">
                <div
                    data-close-button
                    class="flex justify-center items-center w-full px-4 py-2 rounded-md bg-primary text-white font-medium cursor-pointer">
                    Close
                </div>
            </div>
        </div>
    `;
}