import DataTypes from 'sequelize';

export default (sequelize) => {
  const User = sequelize.define('User', {
    id: {
      type: DataTypes.INTEGER,
      primaryKey: true, 
      autoIncrement: true,
      allowNull: false,
    },

  }, {
    timestamps: true, // Adds createdAt and updatedAt
  });

  return User;
};
