import { BakongKHQR, khqrData, IndividualInfo } from "bakong-khqr";

const timestamp = 1788936977378;
const expirationTimestamp = 1788937877377;

// Let's verify BakongKHQR decode
const KHQR = new BakongKHQR();
const decoded = KHQR.decode("00020101021229190015toslengsey@abaa520459995303840540546.505802KH5920TOSLENGSEY BADMINTON6010PHNOM PENH62170113ORD-2026-0001993400131788936977378011317889378773776304E0C8");

console.log("Decoded KHQR:", JSON.stringify(decoded, null, 2));
