import "./bootstrap";
import "./priceAutoCalc";
import "./navbar";
import "./slider";
import "./sidebar";
import "./star-rating";
import "./auth";
import "./modal";
import AOS from 'aos';
import 'aos/dist/aos.css';

document.addEventListener('DOMContentLoaded', function() {
    AOS.init({
        duration: 800,
        once: true,
        offset: 100
    });
})


const modules = {
    historyUser: [
        () => import("./api/search/historyUser"),
        () => import("./api/show/showModalInformationUser"),
    ],
    itemType: [
        () => import("./api/search/itemType"),
        () => import("./api/show/showModalInfoItemType"),
        () => import("./api/show/showModalEditItemType"), 
    ],
    feedback: [
        () => import("./api/search/feedback"),
        () => import("./api/show/showModalInfoFeedback"),
    ],
    canceled: [
        () => import("./api/search/canceled"),
        () => import("./api/show/showModalInfoCanceled"),
    ],
    transaction: [
        () => import("./api/search/transaction"),
        () => import("./api/show/showModalInfoTransaction"),
    ],
    manageUser: [
        () => import("./api/search/user"),
        () => import("./api/show/showModalInfoUser"),
        () => import("./api/show/showModalEditUser"),
    ],
    ironing: [
        () => import("./api/search/ironing"),
        () => import("./api/show/showModalTransaction"),
        () => import("./api/show/showModalInfoIroning"),
        () => import("./api/show/showModalEditIroning"),
    ],
    laundry: [
        () => import("./api/search/laundry"),
        () => import("./api/show/showModalTransaction"),
        () => import("./api/show/showModalInfoLaundry"),
        () => import("./api/show/showModalEditLaundry"),
    ],
};

document.addEventListener("DOMContentLoaded", async () => {
    const moduleName = document.querySelector("[data-module")?.dataset.module;
    const loaders = modules[moduleName];

    if (Array.isArray(loaders)) {
        for (const load of loaders) {
            const module = await load();
            if (module.default) module.default();
        }
    } else {
        console.warn("Module not found or incorrect format:", moduleName);
    }
});
