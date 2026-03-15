document.addEventListener('DOMContentLoaded', function () {
    if (window.modalToShow) {
        if (window.modalToShow === 'modalAddLaundry') {
            initializeModalCalculation("add", "modalAddLaundry");
        }

        if (window.modalToShow === 'modalAddIroning') {
            initializeModalCalculation("add", "modalAddIroning");
        }

        if (window.modalToShow === 'modalEditLaundry') {
            initializeModalCalculation("edit", "modalEditLaundry");
        }

        if (window.modalToShow === 'modalEditIroning') {
            initializeModalCalculation("edit", "modalEditIroning");
        }
    }
})

document.addEventListener('click', function (e) {
    if (e.target.closest('[data-modal-target="modalAddLaundry"]')) {
        initializeModalCalculation("add", "modalAddLaundry");
    }

    if (e.target.closest('[data-modal-target="modalAddIroning"]')) {
        initializeModalCalculation("add", "modalAddIroning");
    }
});

function getModalAddElements(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return null;

    return {
        addressBox: modal.querySelector("#addressBox"),
        retrievalMethod: modal.querySelector("#retrievalMethod"),
        itemCheckboxes: modal.querySelectorAll(".item-checkbox"),
        selectedItemsList: modal.querySelector("#selectedItemsList"),
        noItemsSelected: modal.querySelector("#noItemsSelected"),
        totalDisplay: modal.querySelector("#totalDisplay"),
        totalPriceInput: modal.querySelector("#totalPriceInput"),
        deliveryFee: 20000, // Rp 20,000.00
        subtotalDisplay: modal.querySelector("#subtotalDisplay"),
        deliveryFeeRow: modal.querySelector("#deliveryFeeRow"),
        taxRow: modal.querySelector("#taxRow"),
        taxDisplay: modal.querySelector("#taxDisplay"),
    };
}

function getModalEditElements(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return null;

    return {
        addressBox: modal.querySelector("#addressBox-edit"),
        retrievalMethod: modal.querySelector("#retrievalMethod-edit"),
        itemCheckboxes: modal.querySelectorAll(".item-checkbox-edit"),
        selectedItemsList: modal.querySelector("#selectedItemsList-edit"),
        noItemsSelected: modal.querySelector("#noItemsSelected-edit"),
        totalDisplay: modal.querySelector("#totalDisplay-edit"),
        totalPriceInput: modal.querySelector("#totalPriceInput-edit"),
        deliveryFee: 20000, // Rp 20,000.00
        subtotalDisplay: modal.querySelector("#subtotalDisplay-edit"),
        deliveryFeeRow: modal.querySelector("#deliveryFeeRow-edit"),
        taxRow: modal.querySelector("#taxRow-edit"),
        taxDisplay: modal.querySelector("#taxDisplay-edit"),
    };
}

export default function initializeModalCalculation(type, modalId) {
    const elements = type === 'edit' ? getModalEditElements(modalId) : getModalAddElements(modalId);
    const modal = document.getElementById(modalId);

    const {
        addressBox,
        retrievalMethod,
        itemCheckboxes,
        selectedItemsList,
        noItemsSelected,
        totalDisplay,
        totalPriceInput,
        deliveryFee,
        subtotalDisplay,
        deliveryFeeRow,
        taxRow,
        taxDisplay,
    } = elements;


    // Initialize retrieval method if it was previously selected
    if (retrievalMethod.value === "delivery") {
        deliveryFeeRow.style.display = "flex";
        taxRow.style.display = "flex";
    } else if (retrievalMethod.value === "take_away") {
        deliveryFeeRow.style.display = "none";
        taxRow.style.display = "none";
    }

    // Format number to Indonesian Rupiah
    function formatRupiah(number) {
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 2,
        })
            .format(number)
            .replace("IDR", "Rp");
    }

    // Calculate total price
    function calculateTotal() {
        let total = 0;
        let subtotal = 0;
        let tax = 0;
        let hasSelectedItems = false;

        // Clear the selected items list
        while (selectedItemsList.firstChild) {
            selectedItemsList.removeChild(selectedItemsList.firstChild);
        }

        // Add each selected item to the list and calculate total
        itemCheckboxes.forEach((checkbox) => {
            if (checkbox.checked) {
                hasSelectedItems = true;
                const itemName = checkbox.dataset.name;
                const itemPrice = parseFloat(checkbox.dataset.price);
                const itemValue = checkbox.value;
                const amountInput = modal.querySelector(
                    `input[name="amounts[${itemValue}]"]`
                );

                const quantity = parseInt(amountInput.value) || 1;
                const itemTotal = itemPrice * quantity;

                subtotal += itemTotal;

                // Create item row in summary
                const itemRow = document.createElement("div");
                itemRow.className = "flex justify-between items-center text-sm";
                itemRow.innerHTML = `
                        <div>
                            <span class="font-medium">${itemName}</span>
                            <span class="text-gray-600 text-xs ml-1">(${quantity} × ${formatRupiah(
                    itemPrice
                )})</span>
                        </div>
                        <span>${formatRupiah(itemTotal)}</span>
                    `;
                selectedItemsList.appendChild(itemRow);
            }
        });

        tax = subtotal * 0.1;
        total = subtotal;

        // Add delivery fee if delivery is selected
        if (retrievalMethod.value === "delivery") {
            total += deliveryFee;
            total += tax;
            deliveryFeeRow.style.display = "flex";
            taxRow.style.display = "flex";
        } else {
            deliveryFeeRow.style.display = "none";
            taxRow.style.display = "none";
        }

        // Show "No items selected" if no items are selected
        if (!hasSelectedItems) {
            selectedItemsList.appendChild(noItemsSelected);
        } else {
            // If noItemsSelected is in the DOM, remove it
            if (selectedItemsList.contains(noItemsSelected)) {
                selectedItemsList.removeChild(noItemsSelected);
            }
        }

        // Update total display and hidden input
        taxDisplay.textContent = formatRupiah(tax);
        subtotalDisplay.textContent = formatRupiah(subtotal);
        totalDisplay.textContent = formatRupiah(total);
        totalPriceInput.value = total;
    }

    // Toggle amount input visibility when checkbox is clicked
    itemCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener("change", function () {
            const container = type === 'edit' ? this.closest(".item-container-edit") : this.closest(".item-container");
            const amountInput = type === 'edit' ? container.querySelector(".box-amount-edit") : container.querySelector(".box-amount");

            if (this.checked) {
                amountInput.classList.remove("hidden");
                amountInput.classList.add("block");
            } else {
                amountInput.classList.remove("block");
                amountInput.classList.add("hidden");
            }

            calculateTotal();
        });

        // Initialize amount inputs for checked items
        if (checkbox.checked) {
            const container = type === 'edit' ? checkbox.closest(".item-container-edit") : checkbox.closest(".item-container");
            const amountInput = type === 'edit' ? container.querySelector(".box-amount-edit") : container.querySelector(".box-amount");
            amountInput.classList.remove("hidden");
            amountInput.classList.add("block");
        }
    });

    // Listen for changes in amount inputs
    modal.querySelectorAll(`.amount-input${type === 'edit' ? '-edit' : ''}`).forEach((input) => {
        input.addEventListener("input", calculateTotal);
    });

    // Toggle address box when retrieval method changes
    retrievalMethod.addEventListener("change", function () {
        if (this.value === "delivery") {
            addressBox.style.display = "grid";
        } else {
            addressBox.style.display = "none";
        }

        calculateTotal();
    });

    // Initialize total calculation
    calculateTotal();
}