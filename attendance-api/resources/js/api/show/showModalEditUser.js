import initializeModal from "../../modal";
import fetchDetailToModal from "./fetchDetailToModal";
import buildRoute from "../../utils/buildRoute";

initializeModal("showModalEditUser", async ({ id }) => {
    await fetchDetailToModal({
        id: id,
        url: "/admin/users/edit",
        renderFn: renderModalEditUser,
    });
});

function renderModalEditUser(response) {
    const modal = document.getElementById("modalEditUser");
    const data = response.data;

    if (!data) {
        return;
    }

    const modalContent = modal.querySelector(".modal-data");

    modalContent.innerHTML = `
        <h2 class="text-xl text-center text-primary font-bold tracking-wide mb-4">Edit User</h2>

        <form action="${buildRoute('manage_users_update')}" method="post" class="space-y-4">

            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" name="id" value="${data?.id}">

            <div>
                <label for="username" class="text-sm font-bold text-primary">Username</label>
                <input type="text" name="username" id="username" class="bg-secondary w-full text-primary placeholder:text-primary placeholder:font-bold px-4 py-2 mt-1 rounded-md text-sm outline-0" value="${data.name}">
            </div>
            <div>
                <label for="email" class="text-sm font-bold text-primary">Email</label>
                <input type="email" name="email" id="email" class="bg-secondary w-full text-primary placeholder:text-primary placeholder:font-bold px-4 py-2 mt-1 rounded-md text-sm outline-0" value="${data.email}">
            </div>
            <div>
                <label for="address" class="text-sm font-bold text-primary">Address</label>
                <input type="text" name="address" id="address" class="bg-secondary w-full text-primary placeholder:text-primary placeholder:font-bold px-4 py-2 mt-1 rounded-md text-sm outline-0" value="${data.address ?? ''}">
            </div>
            <div>
                <label for="telp" class="text-sm font-bold text-primary">Telp Number</label>
                <input type="text" name="telp" id="telp" class="bg-secondary w-full text-primary placeholder:text-primary placeholder:font-bold px-4 py-2 mt-1 rounded-md text-sm outline-0" value="${data.telp ?? ''}">
            </div>

            <div class="flex gap-2 bg-white">
                <div
                    data-close-button
                    class="flex justify-center items-center w-full px-4 py-2 rounded-md bg-primary text-white font-medium cursor-pointer">
                    Close
                </div>
                <button
                    type="Submit"
                    class="block w-full px-4 py-2 rounded-md bg-primary text-white font-medium cursor-pointer">
                    Submit
                </button>
            </div>
        </form>
    `;
}
