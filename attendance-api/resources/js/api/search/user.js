import initSearchHandler from "./initSearchHandler";

const userList = document.getElementById("userList");
const paginationContainer = document.getElementById("pagination-container");
const searchInput = document.getElementById("search");

searchInput.addEventListener("input", function (e) {
    initSearchHandler({
        searchValue: e.target.value.trim(),
        apiPath: "/admin/users",
        renderFn: renderUserList,
    });
});

function renderUserList(data) {
    userList.innerHTML = "";
    paginationContainer.innerHTML = "";

    if (data.data.length === 0) {
        userList.innerHTML = `
            <div class="bg-primary text-white w-full rounded-sm flex flex-col justify-center py-3 px-2 items-center">
                <p class="text-white font-bold text-lg">No User Found</p>
            </div>
        `;

        return;
    }

    data.data.forEach((item) => {
        userList.innerHTML += `
            <div
                class="bg-primary text-white w-full rounded-sm flex flex-col justify-center py-3 px-2 items-center">
                <img src="/img/profile-img.png" alt="profile" class="w-25 h-25">
                <h1 class="text-2xl mb-4">${item?.name}</h1>
                <p class="text-sm text-white/50">Joined At</p>
                <p class="mb-2">${new Date(item?.created_at).toLocaleDateString("id-ID", {
                        day: "2-digit",
                        month: "long",
                        year: "numeric",
                    })}
                </p>
                <div class="grid grid-cols-5 gap-4 w-full">
                    <button 
                        data-modal-target="modalInformationUser" 
                        data-id="${item?.id}"
                        data-modal-key="showModalInfoUser"
                        class="bg-white cursor-pointer py-2 rounded-sm text-primary col-span-4">
                        View
                    </button>
                    <button 
                        data-modal-target="modalEditUser"
                        data-id="${item?.id}"
                        data-modal-key="showModalEditUser"
                        class="bg-white cursor-pointer rounded-sm p-2 text-primary flex items-center justify-center col-span-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor"
                            viewBox="0 0 512 512">
                            <path
                                d="M362.7 19.3L314.3 67.7 444.3 197.7l48.4-48.4c25-25 25-65.5 0-90.5L453.3 19.3c-25-25-65.5-25-90.5 0zm-71 71L58.6 323.5c-10.4 10.4-18 23.3-22.2 37.4L1 481.2C-1.5 489.7 .8 498.8 7 505s15.3 8.5 23.7 6.1l120.3-35.4c14.1-4.2 27-11.8 37.4-22.2L421.7 220.3 291.7 90.3z" />
                        </svg>
                    </button>
                </div>
            </div>
        `;
    });

    paginationContainer.innerHTML = data.pagination;
}
