import initializeModal from "../../modal";
import fetchDetailToModal from "./fetchDetailToModal";
import buildRoute from "../../utils/buildRoute";
import { strSlug } from "../../utils/string";
import { formatCurrency} from "../../utils/formatter";

initializeModal('showModalTransaction', async ({ slug }) => {
    await fetchDetailToModal({
        id: strSlug(slug),
        url: "/admin/service/transaction",
        renderFn: renderModalTransaction,
    });
});

function renderModalTransaction(response) {
    const modal = document.getElementById("modalTransaction");
    const modalContent = modal.querySelector(".modal-data");
    const data = response.data;
    
    if (!data) return;

    let priceTotal = data.price_laundry ?? data.price_ironing;
    let deliveryFee = data.retrieval_method === 'delivery' ? 20000 : 0;
    let subTotal = data.retrieval_method === 'delivery' ? (priceTotal - deliveryFee) / 1.1 : priceTotal;
    let tax = data.retrieval_method === 'delivery' ? subTotal * 0.1 : 0;

    modalContent.querySelector('h2').textContent = `Pay ${data?.name_ironing ?? data?.name_laundry}`;
    modalContent.querySelector('input[name="service-type"]').value = data?.name_ironing ?? data?.name_laundry ?? '';

    modalContent.querySelector('#selectedItemsContainer').innerHTML = `
        <h3 class="text-sm font-bold text-primary mb-2">Selected Items:</h3>
        <div id="selectedItemsList-edit" class="space-y-2 max-h-40 overflow-y-auto">
            ${data?.order_items.map(item => `
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
        <input type="hidden" name="total_price" id="totalPriceInput-edit" value="${priceTotal}">
    `;
    
    modalContent.querySelector('input#amount').placeholder = `${data?.amount_item}pcs (${new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
    }).format(data?.price_ironing ?? data?.price_laundry)})`;
    modalContent.querySelector('input#amount').value = `${data?.amount_item}pcs (${new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
    }).format(data?.price_ironing ?? data?.price_laundry)})`;
}