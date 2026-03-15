import api from "../axios";
import { hideLoader, showLoader } from "../../utils/loader";

export default async function fetchDetailToModal({ id, type, url, renderFn }) {
    showLoader();
    let endpoint = `${url}/${id}`;
    if (type) {
        endpoint += `/${type}`;
    }
    
    try {
        const response = await api.get(endpoint);
        if (response.statusText !== "OK") {
            throw new Error(`Error: ${response.statusText}`);
        }

        if (response.data.status === "success") {
            renderFn(response.data);
        }
    } catch (error) {
        console.error("Error fetching data:", error);
    } finally {
        hideLoader();
    }
}