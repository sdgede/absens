import initializeModal from "../../modal";
import fetchDetailToModal from "./fetchDetailToModal";
import buildRoute from "../../utils/buildRoute";

initializeModal('showModalInfoItemType', async ({ id, type }) => {
    await fetchDetailToModal({
        id: id,
        type: type,
        url: "/admin/item-types",
        renderFn: renderModalInformationItemType,
    });
});

function renderModalInformationItemType(response) {
    const modal = document.getElementById("modalInformationType");
    const data = response.data;

    if (!data) {
        return;
    }

    const modalContent = modal.querySelector(".modal-data");

    modalContent.innerHTML = `
        <h2 class="text-xl text-center text-primary font-bold tracking-wide mb-4">
            Type : ${data?.name_item}
            (${data?.role === "laundry" ? "L" : "I"})
        </h2>


        <div class="flex flex-col justify-center space-y-4 items-center">
            <img src="${data?.image_item ? '/storage/' + data?.image_item : ''}" alt="bedding" class="w-full h-70">
            <input type="text" disabled class="bg-secondary w-full placeholder:text-primary placeholder:font-bold px-4 py-2 mt-1 rounded-md text-sm outline-0" placeholder="${data?.name_item} [${new Intl.NumberFormat("id-ID", {
                    style: "currency",
                    currency: "IDR",
                }).format(data?.price_item)}]">
        </div>


        <div class="grid grid-cols-2 mt-4 gap-2 bg-white">
            <div data-close-button
                class="close-button flex justify-center items-center w-full h-fit px-4 py-2 rounded-md bg-primary text-white font-medium cursor-pointer">
                Close
            </div>
            <form action="${buildRoute('item_types_delete', data?.id)}" method="POST" class="inline" onsubmit="return confirm('Are you sure to want delete this?')">
                <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="flex justify-center items-center w-full px-4 py-2 rounded-md bg-red-600 text-white font-medium cursor-pointer">
                    Delete
                </button>
            </form>
        </div>
    `;
}
