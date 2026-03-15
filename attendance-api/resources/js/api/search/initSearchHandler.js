import api from "../axios";
import { getUrlParams, updateQueryString } from "../../utils/queryStr";
import { hideLoader, showLoader } from "../../utils/loader";
import { debounce } from "../../utils/debounce";

async function getDataWithSearch(apiPath, searchValue, renderCallback) {
    const queryString = getUrlParams("search", searchValue);
    showLoader();

    try {
        const response = await api.get(`${apiPath}${queryString}`);
        if (response.statusText !== "OK") {
            throw new Error(`Error: ${response.statusText}`);
        }

        updateQueryString("search", searchValue);

        if (response.data.status === "success") {
            renderCallback(response.data);
        }
    } catch (error) {
        console.error("Error fetching data:", error);
    } finally {
        hideLoader();
    }
}

let debouncedSearchHandler;
export default function initSearchHandler({
    searchValue,
    apiPath,
    renderFn,
    debounceDelay = 500,
}) {
    if (!debouncedSearchHandler) {
        debouncedSearchHandler = debounce((search) => {
            getDataWithSearch(apiPath, search, renderFn);
        }, debounceDelay);
    }

    debouncedSearchHandler(searchValue);
}
