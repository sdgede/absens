import buildRoute from "../../utils/buildRoute";
import initSearchHandler from "./initSearchHandler";
import { formatDate } from "../../utils/formatter";
import { strSlug } from "../../utils/string";

const historyList = document.getElementById("historyList");
const paginationContainer = document.getElementById("pagination-container");
const searchInput = document.getElementById("search");

searchInput.addEventListener("input", function (e) {
    initSearchHandler({
        searchValue: e.target.value.trim(),
        apiPath: "/user/history",
        renderFn: renderHistoryList,
    });
});

function renderHistoryList(data) {
    historyList.innerHTML = "";
    paginationContainer.innerHTML = "";

    if (data.data.length === 0) {
        historyList.innerHTML = `
            <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                <div class="flex flex-col items-center">
                    <div class="bg-gray-100 p-4 rounded-full mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-medium text-gray-700 mb-2">No Orders Found</h3>
                    <p class="text-gray-500 max-w-md mb-6">You don't have any service history yet. Place your first order to get started!</p>
                    <div class="flex items-center justify-center flex-col md:flex-row gap-5 md:gap-8">
                        <a href="${buildRoute('laundry')}" class="bg-primary hover:bg-primary-dark rounded-lg cursor-pointer text-white py-2 px-6 font-medium shadow-lg transition-all duration-300 hover:shadow-xl hover:-translate-y-1 relative overflow-hidden group focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Order Laundry
                        </a>
                        <div class="h-full block m-auto">
                            <p class="text-gray-500 text-lg max-w-md">Or</p>
                        </div>
                        <a href="${buildRoute('ironing')}" class="bg-primary hover:bg-primary-dark rounded-lg cursor-pointer text-white py-2 px-6 font-medium shadow-lg transition-all duration-300 hover:shadow-xl hover:-translate-y-1 relative overflow-hidden group focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Order Ironing
                        </a>
                    </div>
                </div>
            </div>
        `;
        return;
    }

    data.data.forEach((item) => {
        historyList.innerHTML += `
            <div data-modal-target="modalInformationUser"
                class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-all duration-200 cursor-pointer"
                data-id="${item.id}" data-type="${item.type}" data-modal-key="showModalInformationUser">
                <div class="flex flex-col md:flex-row md:items-center justify-between p-4 md:p-5 border-l-4
                    ${item.status === 'pending' ? 'border-yellow-400' : ''}
                    ${item.status === 'process' ? 'border-blue-400' : ''}
                    ${item.status === 'completed' ? 'border-green-500' : ''}">
                    
                    <div class="flex items-start gap-4">
                    <!-- Service Icon -->
                    <div class="hidden md:flex h-12 w-12 rounded-full bg-primary bg-opacity-10 items-center justify-center flex-shrink-0 text-white font-bold tracking-wide">
                        ${item.type.substring(0, 2).toUpperCase()}
                    </div>
                    
                    <!-- Order Details -->
                    <div>
                        <div class="flex items-center gap-2">
                        <h2 class="text-lg font-semibold text-gray-800">${item.name}</h2>
                        <span class="text-xs font-medium uppercase px-2 py-1 rounded-full
                            ${item.type === 'laundry' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'}">
                            ${item.type}
                        </span>
                        </div>
                        
                        <div class="mt-1 text-sm text-gray-600">
                        <div class="flex items-center gap-1 mb-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Submitted on ${formatDate(item.created_at)}</span>
                        </div>
                        
                        ${item.address_delivery ? `
                            <div class="flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="truncate max-w-xs">${item.address_delivery}</span>
                            </div>
                        ` : `
                            <div class="flex items-center gap-1 text-gray-500 italic">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>No delivery address</span>
                            </div>
                        `}
                        </div>
                    </div>
                    </div>
                    
                    <div class="flex items-center justify-between md:justify-end gap-4 mt-4 md:mt-0">
                    <!-- Status Badge -->
                    <div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        ${item.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : ''}
                        ${item.status === 'process' ? 'bg-blue-100 text-blue-800' : ''}
                        ${item.status === 'completed' ? 'bg-green-100 text-green-800' : ''}">
                        ${item.status === 'pending' ? `
                            <svg class="mr-1.5 h-2 w-2 text-yellow-600" fill="currentColor" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3" />
                            </svg>
                        ` : item.status === 'process' ? `
                            <svg class="mr-1.5 h-2 w-2 text-blue-600" fill="currentColor" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3" />
                            </svg>
                        ` : `
                            <svg class="mr-1.5 h-2 w-2 text-green-600" fill="currentColor" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3" />
                            </svg>
                        `}
                        ${item.status.charAt(0).toUpperCase() + item.status.slice(1)}
                        </span>
                    </div>

                    ${item.status !== 'pending' ? `
                        <!-- Action Buttons -->
                        <div class="flex gap-2">
                            <a href="/user/print?type=${item.type}-receipt&service=${strSlug(item.name)}" class="bg-primary text-white p-2 rounded-lg transition-colors duration-200" target="_blank" title="Print Receipt">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            </a>
                        </div>
                    ` : ''}
                    </div>
                </div>
            
            <!-- Progress Indicator (for in-process items) -->
            ${item.status === 'process' ? `
                <div class="bg-blue-50 px-5 py-2">
                <div class="flex items-center justify-between text-xs text-blue-700">
                    <span>Processing</span>
                    <span>Estimated completion: ${formatDate(item.estimation)}</span>
                </div>
                <div class="w-full bg-blue-200 rounded-full h-1.5 mt-1">
                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: 45%"></div>
                </div>
                </div>
            ` : ''}
            </div>
        `;
    });

    paginationContainer.innerHTML = data.pagination;
}
