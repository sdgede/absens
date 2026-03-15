import initializeModal from "../../modal";
import fetchDetailToModal from "./fetchDetailToModal";
import buildRoute from "../../utils/buildRoute";
import { formatCurrency } from "../../utils/formatter";

initializeModal('showModalInfoTransaction', async ({ id }) => {
    await fetchDetailToModal({
        id: id,
        url: "/admin/transaction",
        renderFn: renderModalInfoTransaction,
    });
});

function renderModalInfoTransaction(response) {
    const modal = document.getElementById("modalInformationTransaction");
    const modalContent = modal.querySelector(".modal-data");
    const data = response.data;

    const serviceName = data?.ironing ? 'ironing' : 'laundry';

    if (!data) return;

    let priceTotal = data?.[serviceName]?.[`price_${serviceName}`];
    let deliveryFee = data?.[serviceName]?.retrieval_method === 'delivery' ? 20000 : 0;
    let subTotal = data?.[serviceName]?.retrieval_method === 'delivery' ? (priceTotal - deliveryFee) / 1.1 : priceTotal;
    let tax = data?.[serviceName]?.retrieval_method === 'delivery' ? subTotal * 0.1 : 0;

    modalContent.innerHTML = `
        <h2 class="text-xl text-center text-primary font-bold tracking-wide mb-4">Pay ${data?.[serviceName]?.[`name_${serviceName}`]}</h2>

        <div class="space-y-4">

            <div class="bg-secondary rounded-md p-3 mt-4">
                <h3 class="text-sm font-bold text-primary mb-2">Selected Items:</h3>
                <div id="selectedItemsList-edit" class="space-y-2 max-h-40 overflow-y-auto">
                    ${data?.[serviceName]?.order_items.map(item => `
                        <div class="flex justify-between items-center text-sm">
                            <div>
                                <span c lass="font-medium">${item?.item_type?.name_item}</span>
                                <span class="text-gray-600 text-xs ml-1">(${item?.quantity} × ${formatCurrency(item?.item_type?.price_item)})</span>
                            </div>
                            <span>${formatCurrency(item?.price_total)}</span>
                        </div>
                    `).join('')}
                </div>
        
                <div class="mt-3 pt-2 border-t border-gray-200">
                    <div class="flex justify-between items-center font-medium text-gray-600">
                        <span>Subtotal</span>
                        <span id="subtotalDisplay-edit">${formatCurrency(subTotal)}</span>
                    </div>
                    <div class="flex justify-between items-center font-medium text-gray-600" id="deliveryFeeRow-edit" style="display: flex;">
                        <span>Delivery Fee</span>
                        <span id="deliveryFeeDisplay">${formatCurrency(deliveryFee)}</span>
                    </div>
                    <div class="flex justify-between items-center font-medium text-gray-600" id="taxRow-edit" style="display: flex;">
                        <span>Tax (10%)</span>
                        <span id="taxDisplay-edit">${formatCurrency(tax)}</span>
                    </div>
                    <div class="flex justify-between font-bold text-primary">
                        <span>Total:</span>
                        <span id="totalDisplay-edit">${formatCurrency(priceTotal)}</span>
                        </div>
                </div>
            </div>

            <div>
                <label for="method" class="block text-sm font-bold text-primary mb-4">Transaction Method</label>
                <input type="text" disabled class="col-span-1 bg-secondary w-full text-primary placeholder:text-primary placeholder:font-bold px-4 py-2 rounded-md text-sm outline-0" 
                placeholder="${data?.method.charAt(0).toUpperCase() + data?.method.slice(1)}" 
                value="${data?.method.charAt(0).toUpperCase() + data?.method.slice(1)}">
            </div>

            ${data?.method === 'debit' ? `
                <div class="grid grid-cols-4 gap-2">
                    <input type="text" disabled class="col-span-1 bg-secondary w-full text-primary placeholder:text-primary placeholder:font-bold px-4 py-2 mt-1 rounded-md text-sm outline-0" placeholder="${data?.bank_name}" value="${data?.bank_name}">
                    <input type="text" disabled class="col-span-3 bg-secondary w-full text-primary placeholder:text-primary placeholder:font-bold px-4 py-2 mt-1 rounded-md text-sm outline-0" placeholder="${data?.card_number}" value="${data?.card_number}">
                    <input type="text" disabled class="col-span-4 bg-secondary w-full text-primary placeholder:text-primary placeholder:font-bold px-4 py-2 mt-1 rounded-md text-sm outline-0" placeholder="${data?.postal_code}" value="${data?.postal_code}">
                </div>
                ` : ''}

            <div class="grid grid-cols-2 gap-2 bg-white">
                <div data-close-button
                    class="close-button flex justify-center items-center w-full h-fit px-4 py-2 rounded-md bg-primary text-white font-medium cursor-pointer">
                    Close
                </div>
                <form action="${buildRoute('admin_transaction_delete', data?.id)}" method="POST" class="inline" onsubmit="return confirm('Are you sure to want delete this?')">
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