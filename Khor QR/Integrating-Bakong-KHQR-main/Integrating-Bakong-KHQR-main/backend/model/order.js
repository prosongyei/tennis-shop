import DataTypes from 'sequelize';

export default (sequelize) => {
  const Order = sequelize.define('Order', {
    id: {
      type: DataTypes.INTEGER,
      primaryKey: true, 
        autoIncrement: true,
        allowNull: false,
    },
    userId: {
      type: DataTypes.INTEGER,
      allowNull: false,
      references: {
        model: 'Users',
        key: 'id'
      },
      onDelete: 'CASCADE'
    },
    amount : {
        type : DataTypes.DECIMAL(10, 2),
        allowNull : false,
    },
    status : {
        type : DataTypes.ENUM('pending', 'paid'),
        defaultValue : 'pending'
    },
    currency : {
        type : DataTypes.STRING(3),
        defaultValue : "USD"
    },
    payment_method : {
        type : DataTypes.ENUM("khqr", "credit_card"),
        allowNull : false
    },
    transaction_id : {
        type : DataTypes.STRING(255),
        allowNull : true,
    },

    paid_at : {
        type : DataTypes.TEXT,
        allowNull : true, 
    },

    qr_code : {
        type : DataTypes.TEXT,
        allowNull : true
    },
    qr_md5 : {
        type : DataTypes.STRING(32),
        allowNull : true,
        unique : true,
    },
    qr_expiration : {
        type : DataTypes.BIGINT,
        allowNull: true,
    },


    bakongHash : {
        type : DataTypes.STRING(255),
        allowNull : true
    },
    fromAccountId : {
        type : DataTypes.STRING(100),
        allowNull : true
    },
    toAccountId : {
        type : DataTypes.STRING(100),  //  Changed to STRING
        allowNull : true
    },
    description : {
        type : DataTypes.TEXT,
        allowNull : true
    },
    paid : {
        type : DataTypes.BOOLEAN,
        defaultValue : false
    }


        
    })

    return Order;
}