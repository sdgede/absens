import initializeModal from "../../modal";
import buildRoute from "../../utils/buildRoute";
import fetchDetailToModal from "./fetchDetailToModal";

initializeModal("showModalInfoUser", async ({ id }) => {
    await fetchDetailToModal({
        id: id,
        url: "/admin/users",
        renderFn: renderModalInfoUser,
    });
});

function renderModalInfoUser(response) {
    const modal = document.getElementById("modalInformationUser");
    const data = response.data;

    if (!data) {
        return;
    }

    const modalContent = modal.querySelector(".modal-data");
    modalContent.innerHTML = `
        <h2 class="text-xl text-center text-primary font-bold tracking-wide mb-4">User Data</h2>

        <div class="space-y-4">
            <div class="w-full bg-secondary p-2 px-6 text-primary">
                <p class="text-right mb-2">${data?.email}</p>
                <div class="flex items-center gap-4 mb-2">
                    <div class="h-25 w-25 rounded-full bg-white flex items-center border border-primary justify-center text-primary font-medium text-5xl uppercase">
                        ${data?.name.substr(0, 2)}
                    </div>
                    <div>
                        <h1 class="font-medium text-2xl">${data?.name} (${data?.id})</h1>
                        <p class="text-[.8rem]">${data?.telp ?? 'No Telp'}</p>
                    </div>
                </div>
                <p class="text-right">${new Date(data?.created_at).toLocaleDateString("id-ID", {
                    day: "2-digit",
                    month: "long",
                    year: "numeric",
                })}</p>
            </div>

            <div>
                <label class="text-sm font-bold text-primary">Address User</label>
                <input type="text" disabled class="bg-secondary w-full placeholder:text-primary placeholder:font-bold px-4 py-2 mt-1 rounded-md text-sm outline-0" placeholder="${data?.address}" value="${data?.address ?? 'No Address'}">
            </div>

            <div class="flex flex-col">
                <label class="text-sm font-bold text-primary mb-1">Service History</label>
                <div class="bg-secondary text-primary px-4 py-2 w-full rounded-md resize-none outline-none text-sm">
                    <div class="text-lg space-y-1">
                        <p>Service Created : ${response?.serviceCreated}</p>
                        <p>Feedback Created : ${response?.feedbackCreated}</p>
                        <p>Canceled Service : ${response?.canceledService}</p>
                        <p>Amount Payed Total : ${new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR',
                        }).format(response?.amountPayedTotal)}</p>
                    </div>
                    
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 bg-white">
                <div data-close-button
                    class="close-button flex justify-center items-center w-full h-fit px-4 py-2 rounded-md bg-primary text-white font-medium cursor-pointer">
                    Close
                </div>
                <form action="${buildRoute('admin_user_delete', data?.id)}" method="POST" class="inline" onsubmit="return confirm('Are you sure to want delete this?')">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="flex justify-center items-center w-full px-4 py-2 rounded-md bg-red-600 text-white font-medium cursor-pointer">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    `;
}