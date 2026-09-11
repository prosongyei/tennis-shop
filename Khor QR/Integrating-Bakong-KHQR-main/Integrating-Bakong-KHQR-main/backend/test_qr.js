import { BakongKHQR, khqrData, IndividualInfo } from "bakong-khqr";

const expirationTimestamp = Date.now() + 15 * 60 * 1000;

const optionalData = {
    currency: khqrData.currency.usd,
    amount: 46.50,
    billNumber: "ORD-2026-0001",
    expirationTimestamp: expirationTimestamp
};

const individualInfo = new IndividualInfo(
    "toslengsey@abaa",
    "TOSLENGSEY BADMINTON",
    "PHNOM PENH",
    optionalData
);

const KHQR = new BakongKHQR();
const qrData = KHQR.generateIndividual(individualInfo);

console.log("Full qrData response:", JSON.stringify(qrData, null, 2));
