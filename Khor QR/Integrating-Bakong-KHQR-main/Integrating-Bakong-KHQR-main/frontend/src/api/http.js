import axios from "axios";


const baseURL = '/api';

const http = axios.create({
    baseURL,
    withCredentials : true
})

export default http;