import http from "./http"

export const checkpayment_api = async(id , qr_md5) => {
    const response = await http.post(`/orders/${id}/check_payment`, {qr_md5});

    return response.data;
}