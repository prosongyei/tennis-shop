import db from "../model/index.js";
import axios from 'axios';

export const checkpayment = async (req,res) => {
    const {id} = req.params;
    const {qr_md5} = req.body;

    try{
        const userId = await db.User.findByPk(id);
        if(!userId) return res.status(404).json({success : false, message : 'user not found!'});

        const order = await db.Order.findOne({where : {userId : id, qr_md5}});

        if(order.paid && order.status === 'paid') {
            return res.status(200).json({
                success : true,
                message : "Payment already confirmed",
                data : {
                    id : order.id,
                    bakongHash : order.bakongHash,
                    paid_at : order.paid_at,
                }
            })
        }

        if(order.qr_expiration && Date.now() > order.qr_expiration) {
            return res.status(400).json({
                success : false,
                message : "QR code has expired."
            })
        }

        if(order.qr_md5 !== qr_md5) {
            return res.status(400).json({
                success : false, 
                message : "Invalid QR code"
            })
        }


        if(process.env.BAKONG_PROD_BASE_API_URL && process.env.BAKONG_ACCESS_TOKEN) {
            const response = await axios.post(
                `${process.env.BAKONG_PROD_BASE_API_URL}/check_transaction_by_md5`, {md5 : order.qr_md5},
                {headers : {Authorization : `Bearer ${process.env.BAKONG_ACCESS_TOKEN}`}}

            )
            
            const data = response.data;
            console.log("response : " ,data);
            
            if(data.responseCode === 0 && data.data?.hash) {
                await order.update({
                    bakongHash : data.data.hash,
                    fromAccountId : data.data.fromAccountId,
                    toAccountId : data.data.toAccountId,
                    currency : data.data.currency,
                    amount : data.data.amount,
                    description : data.data.description,
                    paid : true,
                    paid_at : new Date().toISOString(),
                    status : "paid",
                    transaction_id : data.data.hash

                })
                console.log('payment confirmed!✅');

                return res.status(200).json({
                    success : true, 
                    message : "Payment confirmed",
                    data : {
                        id : order.id,
                        bakongHash : data.data.hash,
                        paid_at : order.paid_at
                    }
                })
            }

            else {
                console.log('payment not found!❌');
                return res.status(404).json({
                    success : false,
                    message : "payment not found!",
                })
            }
        }
        else {
            console.log("problem might be : BAKONG_PROD_BASE_API_URL, and BAKONG_ACCESS_TOKEN")
            return res.status(400).json({success : false, message : "Missing required environment variables: BAKONG_PROD_BASE_API_URL or BAKONG_ACCESS_TOKEN"});
        }

        

    }
    catch(error) {
        console.log("payment error : ", error);
        return res.status(500).json({
            success : false, 
            message : error.message
        })
    }
}