import initSearchHandler from "./initSearchHandler";

const itemTypesList = document.getElementById("itemTypesList");
const paginationContainer = document.getElementById("pagination-container");
const searchInput = document.getElementById("search");

searchInput.addEventListener("input", function (e) {
    initSearchHandler({
        searchValue: e.target.value.trim(),
        apiPath: "/admin/item-types",
        renderFn: renderItemType,
    });
});

function renderItemType(data) {
    itemTypesList.innerHTML = "";
    paginationContainer.innerHTML = "";

    if (data.data.length === 0) {
        itemTypesList.innerHTML = `
            <div class="w-full bg-primary rounded-sm p-3 flex flex-col items-center justify-center h-80">
                <img src="" alt="not found" class="w-32 h-32 opacity-70 mb-4">
                <h1 class="text-white text-lg md:text-xl text-center">
                    No data found
                </h1>
                <p class="text-white text-sm mt-1 text-center opacity-80">Try a different keyword or check your spelling.</p>
            </div>
        `;
        return;
    }

    data.data.forEach((item) => {
        itemTypesList.innerHTML += `
            <div class="w-full bg-primary rounded-sm p-3">
                <img src="${item?.image_item ? '/storage/' + item?.image_item : ''}" alt="type item" class="w-full h-60 mb-3">
                <div class="flex items-center justify-between">
                    <h1 class="text-white text-lg md:text-xl">${item?.name_item}
                        (${item?.role === "laundry" ? "L" : "I"})
                    </h1>

                    <div class="flex items-center gap-1">
                        <button data-modal-target="modalInformationType"
                            data-modal-key="showModalInfoItemType"
                            data-id="${item?.id}"
                            data-type="${item?.role}"
                            class="bg-white cursor-pointer p-2 rounded-sm text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor"
                                viewBox="0 0 576 512">
                                <path
                                    d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z" />
                            </svg>
                        </button>
                        <button data-modal-target="modalEditType"
                            data-modal-key="showModalEditItemType"
                            data-id="${item?.id}"
                            data-type="${item?.role}"
                            class="bg-white cursor-pointer p-2 rounded-sm text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor"
                                viewBox="0 0 512 512">
                                <path
                                    d="M362.7 19.3L314.3 67.7 444.3 197.7l48.4-48.4c25-25 25-65.5 0-90.5L453.3 19.3c-25-25-65.5-25-90.5 0zm-71 71L58.6 323.5c-10.4 10.4-18 23.3-22.2 37.4L1 481.2C-1.5 489.7 .8 498.8 7 505s15.3 8.5 23.7 6.1l120.3-35.4c14.1-4.2 27-11.8 37.4-22.2L421.7 220.3 291.7 90.3z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });

    paginationContainer.innerHTML = data.pagination;
}
