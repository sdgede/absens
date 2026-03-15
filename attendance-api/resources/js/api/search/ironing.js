import initSearchHandler from "./initSearchHandler";
import { ucFirst, strSlug } from "../../utils/string";
import buildRoute from "../../utils/buildRoute";

const ironingList = document.getElementById("ironingList");
const paginationContainer = document.getElementById("pagination-container");
const searchInput = document.getElementById("search");

searchInput.addEventListener("input", function (e) {
    initSearchHandler({
        searchValue: e.target.value.trim(),
        apiPath: "/admin/ironing",
        renderFn: renderIroningList,
    });
});

function renderIroningList(data) {
    ironingList.innerHTML = "";
    paginationContainer.innerHTML = "";

    if (data.data.length === 0) {
        ironingList.innerHTML = `
            <tr>
                <td class="px-6 py-4 text-sm text-primary text-center" colspan="7">No ironing data</td>
            </tr>
        `;

        return;
    }

    data.data.forEach((item, index) => {
        ironingList.innerHTML += `
            <tr>
                <td class="px-6 py-4 text-sm text-primary">${(index + 1).toString().padStart(2, '0')}</td>
                <td class="px-6 py-4 text-sm text-primary">${item?.name_ironing}</td>
                <td class="px-6 py-4 text-sm text-primary">${new Date(item?.created_at).toLocaleDateString("id-ID", {
                        day: "2-digit",
                        month: "long",
                        year: "numeric",
                    })}
                </td>
                <td class="px-6 py-4 text-sm text-primary">
                    ${ucFirst(item?.retrieval_method.replace(/_/g, ' '))}
                </td>
                <td class="px-6 py-4 text-sm text-primary">${item?.order_items?.map(item => item?.item_type?.name_item).join(", ")}</td>
                <td class="px-6 py-4">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                        ${
                            item?.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                            item?.status === 'process' ? 'bg-blue-100 text-blue-800' :
                            item?.status === 'completed' ? 'bg-green-100 text-green-800' : ''
                        }
                        ">${ucFirst(item?.status)}</span>
                </td>
                <td class="px-6 py-4 flex items-center gap-2 text-sm text-gray-500">
                    ${item?.transaction?.length == 0 && item?.status_report === 'normal' ? `
                        <button 
                            data-modal-target="modalTransaction" 
                            data-slug="${item?.name_ironing}"
                            data-modal-key="showModalTransactionIroning" class="cursor-pointer mr-3">
                            <img src="/img/cash.svg" alt="cash" class="w-5 h-5">
                        </button>
                    ` : ''}
                    <button 
                        data-modal-target="modalInformationIroning" 
                        data-id="${item?.id}"
                        data-modal-key="showModalInfoIroning"
                        class="text-blue-500 hover:text-blue-700 cursor-pointer mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor"
                            viewBox="0 0 576 512">
                            <path
                                d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z" />
                        </svg>
                    </button>
                    <button 
                        data-modal-target="modalEditIroning"
                        data-id="${item?.id}"
                        data-modal-key="showModalEditIroning"
                        class="text-gray-500 hover:text-gray-700 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor"
                            viewBox="0 0 512 512">
                            <path
                                d="M362.7 19.3L314.3 67.7 444.3 197.7l48.4-48.4c25-25 25-65.5 0-90.5L453.3 19.3c-25-25-65.5-25-90.5 0zm-71 71L58.6 323.5c-10.4 10.4-18 23.3-22.2 37.4L1 481.2C-1.5 489.7 .8 498.8 7 505s15.3 8.5 23.7 6.1l120.3-35.4c14.1-4.2 27-11.8 37.4-22.2L421.7 220.3 291.7 90.3z" />
                        </svg>
                    </button>
                    ${item?.transaction?.length > 0 ? `
                        <a href="${buildRoute('admin_print', ['ironing-receipt', strSlug(item?.name_ironing)])}" target="_blank" id="printLink">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor"
                                viewBox="0 0 512 512">
                                <path
                                    d="M128 0C92.7 0 64 28.7 64 64l0 96 64 0 0-96 226.7 0L384 93.3l0 66.7 64 0 0-66.7c0-17-6.7-33.3-18.7-45.3L400 18.7C388 6.7 371.7 0 354.7 0L128 0zM384 352l0 32 0 64-256 0 0-64 0-16 0-16 256 0zm64 32l32 0c17.7 0 32-14.3 32-32l0-96c0-35.3-28.7-64-64-64L64 192c-35.3 0-64 28.7-64 64l0 96c0 17.7 14.3 32 32 32l32 0 0 64c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-64zM432 248a24 24 0 1 1 0 48 24 24 0 1 1 0-48z" />
                            </svg>
                        </a>
                    ` : ''}
                </td>
            </tr>
        `;
    });

    paginationContainer.innerHTML = data.pagination;
}
