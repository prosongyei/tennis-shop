import { Router } from "express";
import { generatekhqr } from "../controller/generatekhqr.controller.js";

const router = Router();

router.post("/orders/:id/generate_qrcode", generatekhqr)

export default router;