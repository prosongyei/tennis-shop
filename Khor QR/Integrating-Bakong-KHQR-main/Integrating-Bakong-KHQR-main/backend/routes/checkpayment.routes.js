import { Router } from "express";
import { checkpayment } from "../controller/checkpayment.controller.js";


const router = Router();


router.post('/orders/:id/check_payment', checkpayment);

export default router;