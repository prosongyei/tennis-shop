import db from "../model/index.js";
import {
BakongKHQR,
khqrData,
IndividualInfo,
MerchantInfo,
} from "bakong-khqr";

export const generatekhqr = async(req,res) => {
    const {id} = req.params;

    try{
        let userId = await db.User.findByPk(id);

        if(!userId) {
            userId = await db.User.create({
                id : id
            })
        }

        const order = await db.Order.create({
            userId : userId.id,
            amount : 0.1,
            status : "pending",
            currency : "USD",
            payment_method : "khqr",
            paid : false
        })

        const expirationTimestamp = Date.now() + 5 *60*1000;

        const optionalData = {
            currency : khqrData.currency.usd,
            amount : parseFloat(order.amount),
            expirationTimestamp

        }

        const individualInfo = new IndividualInfo(
            process.env.BAKONG_ACCOUNT_USERNAME,
            process.env.BAKONG_ACCOUNT_NAME,
            "PHNOM PENH",
            optionalData
        )

        const KHQR = new BakongKHQR();
        const qrData = KHQR.generateIndividual(individualInfo);

        console.log("qr: " + qrData.data.qr);
        console.log("md5: " + qrData.data.md5);

        if(!qrData || !qrData.data || !qrData.data.qr) {
            throw new Error("khqr generation failed")
        }

        await db.sequelize.transaction(async(transaction) => {
            await order.update({
                currency : "USD",
                qr_code : qrData.data.qr,
                qr_md5 : qrData.data.md5,
                qr_expiration : expirationTimestamp,
                payment_method : "khqr",
            }, {transaction})
        })

        return res.status(201).json({
            success : true, 
            message : "khqr generated successfully!",
            data : {
                marchant_name : process.env.BAKONG_ACCOUNT_NAME,
                id : order.id,
                qr_code : order.qr_code,
                qr_md5 : order.qr_md5,
                amount : order.amount,
                currency : order.currency,
                qr_expiration : new Date(order.qr_expiration).toISOString()
            }
        })


    }
    catch(error) {
        return res.status(500).json({
            success : false,
            message : "Failed to genereate khqr",
            error : error.message
        })
    }
}