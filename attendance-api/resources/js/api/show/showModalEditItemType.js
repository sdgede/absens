import initializeModal from "../../modal";
import fetchDetailToModal from "./fetchDetailToModal";
import buildRoute from "../../utils/buildRoute";

initializeModal('showModalEditItemType', async ({ id, type }) => {
    await fetchDetailToModal({
        id: id,
        type: type,
        url: "/admin/item-types",
        renderFn: renderModalEditItemType,
    });
});

function renderModalEditItemType(response) {
    const modal = document.getElementById("modalEditType");
    const data = response.data;

    if (!data) {
        return;
    }

    const modalContent = modal.querySelector(".modal-data");

    modalContent.innerHTML = `
        <h2 class="text-xl text-center text-primary font-bold tracking-wide mb-4">
            Type : ${data?.name_item}
        </h2>

        <form action="${buildRoute('item_types_update')}" method="post" enctype="multipart/form-data" class="space-y-4 flex flex-col gap-2">
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" name="id" value="${data?.id}">

            <div class="p-4 w-full h-55 bg-cover bg-center bg-no-repeat rounded-md space-y-4"
                style="background-image: url('${data?.image_item ? '/storage/' + data?.image_item : ''}')">
                <label for="file-upload" class="inline-block bg-primary p-2 rounded-md cursor-pointer">
                    <img src="/img/upload2.svg" alt="upload" class="w-5 h-5">
                </label>
                <input type="file" id="file-upload" name="image_item" accept="image/*" class="hidden">
            </div>

            <div class="space-y-4 flex flex-col gap-2">
                <label class="text-sm font-bold text-primary mb-1">Fill Field</label>
                <input type="text"
                    class="bg-secondary text-primary w-full placeholder:text-primary placeholder:font-bold px-4 py-2 mt-1 rounded-md text-sm outline-0"
                    placeholder="Enter item name" value="${data?.name_item}" name="name_item">

                <input type="number"
                    class="bg-secondary text-primary w-full placeholder:text-primary placeholder:font-bold px-4 py-2 mt-1 rounded-md text-sm outline-0"
                    placeholder="Enter item price" 
                    value="${(data?.price_item)}"
                    name="price_item">
                    
                <div class="relative inline-block w-full">
                    <select
                        class="appearance-none bg-secondary text-primary w-full placeholder:text-primary font-bold px-4 py-2 mt-1 rounded-md text-sm outline-0"
                        id="role" name="role">
                        <option value="" disabled selected>Choose service for item</option>
                        <option value="ironing" ${data?.role === 'ironing' ? 'selected' : ''} class="font-bold">Ironing</option>
                        <option value="laundry" ${data?.role === 'laundry' ? 'selected' : ''} class="font-bold">Laundry</option>
                    </select>

                    <div
                        class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                        <svg class="w-8 h-8 fill-current text-primary" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20">
                            <path d="M5.5 7l4.5 4 4.5-4H5.5z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class=" flex gap-2 bg-white">
                <div data-close-button
                    class="flex justify-center items-center w-full h-fit px-4 py-2 rounded-md bg-primary text-white font-medium cursor-pointer">
                    Close
                </div>
                <button
                    type="Submit"
                    class="block w-full px-4 py-2 rounded-md bg-primary text-white font-medium cursor-pointer">
                    Save
                </button>
            </div>
        </form>
    `;
}
