import { sequelize } from "../config/sequelize.js";
import orderModel from "./order.js";
import userModel from "./user.js";

const User = userModel(sequelize);
const Order = orderModel(sequelize);

// Define relationships
User.hasMany(Order, {
  foreignKey: 'userId',
  as: 'orders',
  onDelete: 'CASCADE'
});

Order.belongsTo(User, {
  foreignKey: 'userId',
  as: 'user'
});

const db = { sequelize, User, Order };
export default db;


// Sync database (creates tables if they don't exist)
export const initDB = async () => {
    try {
        await sequelize.authenticate();
        console.log(' Database connection established successfully');
        
        await sequelize.sync({ force: true }); // Use { force: true } to drop tables on restart
        console.log(' Database models synchronized');
    } catch (error) {
        console.error(' Unable to connect to the database:', error);
    }
};



