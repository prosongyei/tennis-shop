import http from "./http"

export const generatekhqr_api = async (id) => {
    const response = await http.post(`/orders/${id}/generate_qrcode`);
    return response.data;
}