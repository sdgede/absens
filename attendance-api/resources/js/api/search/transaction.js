import initSearchHandler from "./initSearchHandler";

const transactionList = document.getElementById("transactionList");
const paginationContainer = document.getElementById("pagination-container");
const searchInput = document.getElementById("search");

searchInput.addEventListener("input", function (e) {
    initSearchHandler({
        searchValue: e.target.value.trim(),
        apiPath: "/admin/transaction",
        renderFn: renderTransactionList,
    });
});

function renderTransactionList(data) {
    transactionList.innerHTML = "";
    paginationContainer.innerHTML = "";

    if (data.data.length === 0) {
        transactionList.innerHTML = `
            <tr>
                <td class="px-6 py-4 text-md text-primary text-center" colspan="6">No transaction data</td>
            </tr>
        `;

        return;
    }

    data.data.forEach((item, index) => {
        transactionList.innerHTML += `
            <tr>
                <td class="px-6 py-4 text-sm text-primary">${(index + 1).toString().padStart(2, '0')}</td>
                <td class="px-6 py-4 text-sm text-primary">${item?.ironing?.name_ironing ?? item?.laundry?.name_laundry}</td>
                <td class="px-6 py-4 text-sm text-primary">${new Date(item?.created_at).toLocaleDateString("id-ID", {
                        day: "2-digit",
                        month: "long",
                        year: "numeric",
                    })}
                </td>
                <td class="px-6 py-4 text-sm text-primary">${new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                    }).format(item?.price_transaction)}
                </td>
                <td class="px-6 py-4 text-sm text-primary">${item?.method.charAt(0).toUpperCase() + item?.method.slice(1)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <button 
                        data-modal-target="modalInformationTransaction" 
                        data-id="${item?.id}"
                        data-modal-key="showModalInfoTransaction"
                        class="text-blue-500 hover:text-blue-700 cursor-pointer mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor"
                            viewBox="0 0 576 512">
                            <path
                                d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z" />
                        </svg>
                    </button>
                </td>
            </tr>
        `;
    });

    paginationContainer.innerHTML = data.pagination;
}
