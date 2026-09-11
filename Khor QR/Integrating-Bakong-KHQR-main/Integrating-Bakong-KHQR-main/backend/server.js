import 'dotenv/config';
import express from 'express';
import cors from 'cors';
import { initDB } from "./model/index.js";
import generate_khqr_routes from './routes/generatekhqr.routes.js';
import check_khqr_routes from './routes/checkpayment.routes.js';
const app = express();
const PORT = process.env.PORT || 3000;

app.use(cors());
app.use(express.json());

app.use('/api',generate_khqr_routes);
app.use('/api',check_khqr_routes);

initDB();

app.listen(PORT, () => {
    console.log(`Server is running on port ${PORT}`);
});