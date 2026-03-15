import initializeModal from "../../modal";
import buildRoute from "../../utils/buildRoute";
import fetchDetailToModal from "./fetchDetailToModal";
import { strSlug } from "../../utils/string";
import { formatCurrency, formatDate, formatSnakeCaseToLabel } from "../../utils/formatter";

initializeModal('showModalInformationUser', async ({ id, type }) => {
    await fetchDetailToModal({
        id: id,
        type: type,
        url: "/user/history",
        renderFn: renderModalInformationUser,
    });
});

function renderModalInformationUser(response) {
    const modal = document.getElementById("modalInformationUser");
    const data = response.data;

    if (!data) {
        return;
    }

    const modalTitle = modal.querySelector(".modal-title");
    modalTitle.textContent = data.name_ironing ?? data.name_laundry;

    const itemsDetails = modal.querySelector(".items-details");
    
    if (data.order_items.length <= 1) {
        itemsDetails.classList.add("justify-center", "items-center");
    } else {
        itemsDetails.classList.remove("justify-center", "items-center");
    }

    itemsDetails.innerHTML = `
        ${data.order_items.map((item) => `
            <div class="item-card flex flex-col justify-center items-center md:flex-row gap-4 min-w-[95%] md:min-w-[500px]">
                <!-- Item Image -->
                <div class="w-full md:w-1/3 flex justify-center">
                    <div
                        class="relative w-40 h-40 rounded-lg overflow-hidden border border-gray-200 shadow-sm">
                        <img src="${'/storage/' + item?.item_type?.image_item}" alt="Laundry Item"
                            class="w-full h-full object-cover">
                    </div>
                </div>

                <!-- Item Info -->
                <div class="w-full space-y-3 pb-0">
                    <div>
                        <h4 class="text-lg font-medium text-gray-800">${item?.item_type?.name_item}</h4>
                        <p class="text-sm text-gray-600">Order ID: ${String(item?.id).padStart(4, '0')}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="bg-white p-2 rounded-lg border border-gray-300">
                            <span class="text-xs text-gray-500">Price</span>
                            <p class="font-semibold text-sm text-gray-800">${formatCurrency(item?.item_type?.price_item)}</p>
                        </div>
                        <div class="bg-white p-2 rounded-lg border border-gray-300">
                            <span class="text-xs text-gray-500">Quantity</span>
                            <p class="font-semibold text-sm text-gray-800">${item?.quantity} pcs</p>
                        </div>
                        <div class="bg-white p-2 rounded-lg border border-gray-300 col-span-2">
                            <span class="text-xs text-gray-500">Total</span>
                            <p class="font-semibold text-sm text-gray-800">${formatCurrency(item?.price_total)}</p>
                        </div>
                    </div>
                </div>
            </div>
        `).join('')}
    `;


    modal.querySelector('.amount-item').textContent = `${data?.amount_item}pcs (${formatCurrency(data.price_ironing ?? data.price_laundry)})`;
    modal.querySelector('.retrieval-method').textContent = formatSnakeCaseToLabel(data.retrieval_method);

    if (data.retrieval_method === 'delivery') {
        modal.querySelector('.address-container').style.display = 'block';
        modal.querySelector('.destination-container').style.display = 'block';
        modal.querySelector('.delivery-note').style.display = 'flex';
        modal.querySelector('.address').textContent = data.address_taking;
        modal.querySelector('.destination').textContent = data.address_delivery;
    } else {
        modal.querySelector('.address-container').style.display = 'none';
        modal.querySelector('.destination-container').style.display = 'none';
        modal.querySelector('.delivery-note').style.display = 'none';
    }

    modal.querySelector('.notes').textContent = data.notes_laundry ?? data.notes_ironing ?? 'Nothing';
    modal.querySelector('.estimation').textContent = data.estimation ? formatDate(data.estimation) : 'Null Pay First';


    if (!response.hasTransaction) {
        modal.querySelector('.action-buttons').innerHTML = `
            <a href="${buildRoute('transaction', strSlug(data.name_ironing ?? data.name_laundry))}"
                class="w-full bg-primary hover:bg-primary-dark rounded-lg cursor-pointer text-white py-3 px-6 font-bold text-lg shadow-lg transition-all duration-300 hover:shadow-xl hover:-translate-y-1 relative overflow-hidden focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary flex justify-center items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="4" width="20" height="16" rx="2" />
                    <path d="M12 16v.01" />
                    <path d="M8 12h8" />
                    <path d="M8 8h8" />
                </svg>
                Complete Transaction
            </a>
            <button data-modal-target="modalCancelService" data-fetch="false"
                class="cursor-pointer flex justify-center items-center w-full px-4 py-3 rounded-lg bg-white border border-red-200 text-red-600 font-medium shadow-sm hover:bg-red-100 hover:border-red-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="15" y1="9" x2="9" y2="15" />
                    <line x1="9" y1="9" x2="15" y2="15" />
                </svg>
                Cancel Order
            </button>
        `;
    } else {
        modal.querySelector('.action-buttons').innerHTML = '';
    }

    // Data for the modal cancel service
    const modalCancelService = document.getElementById("modalCancelService");
    if (modalCancelService) {
        modalCancelService.querySelector('input[name="order_id"]').value =
            data?.id;
        modalCancelService.querySelector('input[name="service_type"]').value =
            response?.serviceType;
    }
}