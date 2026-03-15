import initializeModal from "../../modal";
import fetchDetailToModal from "./fetchDetailToModal";
import buildRoute from "../../utils/buildRoute";
import { formatDate } from "../../utils/formatter";

initializeModal('showModalInfoCanceled', async ({ id }) => {
    await fetchDetailToModal({
        id: id,
        url: "/admin/canceled",
        renderFn: renderModalInfoCanceled,
    });
});

function renderModalInfoCanceled(response) {
    const modal = document.getElementById("modalInformationCanceled");
    const modalContent = modal.querySelector(".modal-data");
    const data = response.data;

    if (!data) return;

    modalContent.innerHTML = `
        <h2 class="text-xl text-center text-primary font-bold tracking-wide mb-4">Canceled [${data?.id}]</h2>

        <form action="" method="" class="space-y-4">

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

            <div>
                <label class="text-sm font-bold text-primary">Name</label>
                <input type="text" disabled class="bg-secondary w-full placeholder:text-primary placeholder:font-bold px-4 py-2 mt-1 rounded-md text-sm outline-0" placeholder="${data?.ironing?.name_ironing ?? data?.laundry?.name_laundry}" value="${data?.ironing?.name_ironing ?? data?.laundry?.name_laundry}">
            </div>

            <div class="flex flex-col">
                <label for="notes" class="text-sm font-bold text-primary mb-1">Issue</label>
                <textarea name="notes" id="notes" disabled placeholder="${data?.issues}"
                    class="bg-secondary placeholder:text-primary px-4 py-2 w-full h-32 rounded-md resize-none outline-none text-sm">${data?.issues}</textarea>
            </div>

            <div class=" bg-white">
                <div
                    data-close-button
                    class="flex justify-center items-center w-full px-4 py-2 rounded-md bg-primary text-white font-medium cursor-pointer">
                    Close
                </div>
            </div>
        </form>
    `;
}