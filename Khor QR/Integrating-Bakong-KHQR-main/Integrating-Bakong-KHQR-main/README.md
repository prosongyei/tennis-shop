# Integrating Bakong KHQR Payment System

A full-stack application that integrates Cambodia's Bakong KHQR payment system, enabling merchants to generate QR codes for payments and verify transactions in real-time. Built with React (frontend) and Node.js/Express (backend), this project demonstrates a complete payment flow using the Bakong API.

## 📺 Video Tutorial

**[Watch the Full Tutorial on YouTube](https://youtu.be/GTZ_eLx_ar0?si=FAhGzMHd4lm6lVh7)** - Step-by-step guide to building this project from scratch

## Table of Contents

- [What is Bakong KHQR?](#what-is-bakong-khqr)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Configuration](#configuration)
- [Running the Application](#running-the-application)
- [API Endpoints](#api-endpoints)
- [How It Works](#how-it-works)
- [Database Schema](#database-schema)
- [Environment Variables](#environment-variables)
- [Troubleshooting](#troubleshooting)
- [License](#license)

## What is Bakong KHQR?

Bakong is Cambodia's national payment system developed by the National Bank of Cambodia (NBC). KHQR (Khmer QR) is the standardized QR code payment system that enables instant money transfers between bank accounts and digital wallets. This project integrates with Bakong's API to:

- Generate payment QR codes
- Process transactions
- Verify payment status
- Handle payment confirmations

## Features

- **QR Code Generation**: Dynamically generate KHQR codes for specific payment amounts
- **Real-time Payment Verification**: Automatically check payment status via polling
- **Transaction Management**: Store and track orders with payment status
- **QR Code Expiration**: Time-limited QR codes (5 minutes default) for security
- **User Management**: Track users and their transactions
- **Payment Confirmation**: Verify payments through Bakong API
- **Responsive UI**: Modern React frontend with TailwindCSS
- **Database Persistence**: MySQL database for storing users, orders, and transactions

## Tech Stack

### Backend

- **Node.js** - Runtime environment
- **Express** - Web framework
- **Sequelize** - ORM for MySQL
- **MySQL** - Database
- **bakong-khqr** - Official Bakong KHQR library
- **Axios** - HTTP client for API requests
- **dotenv** - Environment configuration
- **CORS** - Cross-origin resource sharing

### Frontend

- **React 19** - UI framework
- **Vite** - Build tool and dev server
- **TailwindCSS** - Utility-first CSS framework
- **qrcode.react** - QR code component
- **Axios** - API communication

## Project Structure

```
Integrating-Bakong-KHQR/
├── backend/
│   ├── config/
│   │   └── sequelize.js          # Database configuration
│   ├── controller/
│   │   ├── generatekhqr.controller.js   # QR generation logic
│   │   └── checkpayment.controller.js   # Payment verification logic
│   ├── model/
│   │   ├── index.js              # Database initialization
│   │   ├── user.js               # User model
│   │   └── order.js              # Order model
│   ├── routes/
│   │   ├── generatekhqr.routes.js
│   │   └── checkpayment.routes.js
│   ├── package.json
│   ├── server.js                 # Entry point
│   └── .env                      # Environment variables (create this)
├── frontend/
│   ├── src/
│   │   ├── api/
│   │   │   ├── generatekhqr.api.js
│   │   │   ├── checkpayment.api.js
│   │   │   └── http.js           # Axios instance
│   │   ├── assets/
│   │   ├── App.jsx               # Main component
│   │   ├── App.css
│   │   ├── main.jsx
│   │   └── index.css
│   ├── public/
│   ├── index.html
│   ├── package.json
│   ├── vite.config.js
│   └── eslint.config.js
└── README.md
```

## Prerequisites

Before you begin, ensure you have the following installed:

- **Node.js** (v18 or higher) - [Download](https://nodejs.org/)
- **MySQL** (v8 or higher) - [Download](https://dev.mysql.com/downloads/)
- **npm** or **yarn** - Package manager (comes with Node.js)
- **Bakong API Credentials**:
  - Bakong account username
  - Bakong account name
  - Bakong access token (for production)
  - Bakong production API URL

### Bakong KHQR Resources

Before starting, review these official Bakong documentation resources:

- **[QR Payment Integration Guide](https://bakong.nbc.gov.kh/download/KHQR/integration/QR%20Payment%20Integration.pdf)** - Complete guide for integrating KHQR payments
- **[KHQR SDK Documentation](https://bakong.nbc.gov.kh/download/KHQR/integration/KHQR%20SDK%20Document.pdf)** - Official SDK documentation and implementation details
- **[Production API](https://api-bakong.nbc.gov.kh/)** - Live Bakong API endpoint
- **[Sandbox/Testing API](https://sit-api-bakong.nbc.gov.kh/)** - Testing environment for development

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/Integrating-Bakong-KHQR.git
cd Integrating-Bakong-KHQR
```

### 2. Setup Backend

```bash
cd backend
npm install
```

### 3. Setup Frontend

```bash
cd ../frontend
npm install
```

### 4. Setup MySQL Database

1. Open MySQL command line or MySQL Workbench
2. Create a new database:

```sql
CREATE DATABASE bakong_khqr;
```

3. (Optional) Create a dedicated user:

```sql
CREATE USER 'bakong_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON bakong_khqr.* TO 'bakong_user'@'localhost';
FLUSH PRIVILEGES;
```

## Configuration

### Backend Environment Variables

Create a `.env` file in the `backend/` directory:

```bash
cd backend
touch .env  # On Windows: type nul > .env
```

Add the following environment variables:

```env
# Server Configuration
PORT=3000

# Database Configuration
DB_NAME=bakong_khqr
DB_USER=root
DB_PASSWORD=your_mysql_password
DB_HOST=localhost
DB_DIALECT=mysql

# Bakong API Configuration
BAKONG_ACCOUNT_USERNAME=your_bakong_username
BAKONG_ACCOUNT_NAME=Your Business Name
BAKONG_ACCESS_TOKEN=your_bakong_access_token
BAKONG_PROD_BASE_API_URL=https://api-bakong.nbc.gov.kh/v1
```

### Frontend Configuration

Update the API base URL in `frontend/src/api/http.js` if your backend runs on a different port:

```javascript
const instance = axios.create({
  baseURL: "http://localhost:3000/api",
});
```

## Running the Application

### Option 1: Run Both Servers Separately

**Terminal 1 - Backend:**

```bash
cd backend
npm start
```

The backend server will start on `http://localhost:3000`

**Terminal 2 - Frontend:**

```bash
cd frontend
npm run dev
```

The frontend will start on `http://localhost:5173` (or another port if 5173 is busy)

### Option 2: Add Concurrently (Optional)

For running both servers with one command, add `concurrently` to the root:

```bash
# In root directory
npm init -y
npm install concurrently --save-dev
```

Add to root `package.json`:

```json
{
  "scripts": {
    "dev": "concurrently \"cd backend && npm start\" \"cd frontend && npm run dev\"",
    "backend": "cd backend && npm start",
    "frontend": "cd frontend && npm run dev"
  }
}
```

Then run:

```bash
npm run dev
```

## API Endpoints

### Generate KHQR Code

**Endpoint:** `POST /api/generate-khqr/:id`

**Description:** Generates a KHQR payment code for a user

**Parameters:**

- `id` (path parameter) - User ID

**Response:**

```json
{
  "success": true,
  "message": "khqr generated successfully!",
  "data": {
    "marchant_name": "Your Business Name",
    "id": 1,
    "qr_code": "khqr://....",
    "qr_md5": "abc123...",
    "amount": 0.1,
    "currency": "USD",
    "qr_expiration": "2026-01-24T10:15:00.000Z"
  }
}
```

### Check Payment Status

**Endpoint:** `POST /api/check-payment/:id`

**Description:** Verifies if payment has been completed

**Parameters:**

- `id` (path parameter) - User ID

**Body:**

```json
{
  "qr_md5": "abc123..."
}
```

**Response (Payment Confirmed):**

```json
{
  "success": true,
  "message": "Payment confirmed",
  "data": {
    "id": 1,
    "bakongHash": "transaction_hash",
    "paid_at": "2026-01-24T10:12:00.000Z"
  }
}
```

**Response (Payment Not Found):**

```json
{
  "success": false,
  "message": "payment not found!"
}
```

## How It Works

### Payment Flow

1. **User Initiates Payment**
   - User enters their ID in the frontend
   - Clicks "Generate QR Code"

2. **QR Code Generation**
   - Frontend sends request to `/api/generate-khqr/:id`
   - Backend creates/finds user in database
   - Creates new order with pending status
   - Generates KHQR code using bakong-khqr library
   - Stores QR data (code, MD5 hash, expiration) in database
   - Returns QR code to frontend

3. **Display QR Code**
   - Frontend displays QR code
   - Shows countdown timer (2 minutes)
   - Starts polling for payment status every 2 seconds

4. **User Scans and Pays**
   - User scans QR code with Bakong app
   - Completes payment in their banking app

5. **Payment Verification**
   - Frontend polls `/api/check-payment/:id` every 2 seconds
   - Backend queries Bakong API to check transaction status
   - If payment found, updates order status to "paid"
   - Returns payment confirmation

6. **Payment Confirmed**
   - Frontend stops polling
   - Displays success message
   - Shows transaction details

### Database Flow

```
User → Order (with pending status) → KHQR Generated →
Payment Completed → Order Updated (paid status) → Transaction Stored
```

## Database Schema

### Users Table

```sql
CREATE TABLE Users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  createdAt DATETIME,
  updatedAt DATETIME
);
```

### Orders Table

```sql
CREATE TABLE Orders (
  id INT PRIMARY KEY AUTO_INCREMENT,
  userId INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  status ENUM('pending', 'paid') DEFAULT 'pending',
  currency VARCHAR(3) DEFAULT 'USD',
  payment_method ENUM('khqr', 'credit_card') NOT NULL,
  transaction_id VARCHAR(255),
  paid_at TEXT,
  qr_code TEXT,
  qr_md5 VARCHAR(255),
  qr_expiration BIGINT,
  bakongHash VARCHAR(255),
  fromAccountId VARCHAR(255),
  toAccountId VARCHAR(255),
  description TEXT,
  paid BOOLEAN DEFAULT FALSE,
  createdAt DATETIME,
  updatedAt DATETIME,
  FOREIGN KEY (userId) REFERENCES Users(id) ON DELETE CASCADE
);
```

## Environment Variables

| Variable                   | Description                  | Example                            |
| -------------------------- | ---------------------------- | ---------------------------------- |
| `PORT`                     | Backend server port          | `3000`                             |
| `DB_NAME`                  | MySQL database name          | `bakong_khqr`                      |
| `DB_USER`                  | MySQL username               | `root`                             |
| `DB_PASSWORD`              | MySQL password               | `your_password`                    |
| `DB_HOST`                  | MySQL host                   | `localhost`                        |
| `DB_DIALECT`               | Database type                | `mysql`                            |
| `BAKONG_ACCOUNT_USERNAME`  | Your Bakong account username | `username`                         |
| `BAKONG_ACCOUNT_NAME`      | Your merchant display name   | `My Shop`                          |
| `BAKONG_ACCESS_TOKEN`      | Bakong API access token      | `your_token_here`                  |
| `BAKONG_PROD_BASE_API_URL` | Bakong API base URL          | `https://api-bakong.nbc.gov.kh/v1` |

## Troubleshooting

### Common Issues

**1. Database Connection Error**

```
Error: ER_ACCESS_DENIED_ERROR
```

**Solution:** Check your MySQL credentials in `.env` file

**2. Cannot Generate QR Code**

```
Error: khqr generation failed
```

**Solution:** Verify your Bakong account credentials in `.env`

**3. Payment Check Fails**

```
Error: Missing required environment variables
```

**Solution:** Ensure `BAKONG_PROD_BASE_API_URL` and `BAKONG_ACCESS_TOKEN` are set

**4. CORS Error**

```
Access to XMLHttpRequest has been blocked by CORS policy
```

**Solution:** Check that CORS is enabled in `server.js` and frontend is configured correctly

**5. Frontend Cannot Connect to Backend**

- Verify backend is running on port 3000
- Check `baseURL` in `frontend/src/api/http.js`
- Ensure no firewall blocking the connection

**6. QR Code Expired**

- QR codes expire after 2 minutes by default
- Generate a new QR code for new transactions
- Modify expiration time in `generatekhqr.controller.js`:

```javascript
const expirationTimestamp = Date.now() + 5 * 60 * 1000; // 5 minutes
```

## Testing

### Test the Flow

1. Start both backend and frontend
2. Open browser to `http://localhost:5173`
3. Enter a user ID (e.g., "1")
4. Click "Generate QR Code"
5. QR code should appear with countdown
6. Use Bakong app to scan and pay (if in production)
7. Payment status should update automatically

### Development Mode

For testing without actual payments, you can modify `checkpayment.controller.js` to simulate successful payments after a delay.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Contact

For questions or support, please open an issue in the GitHub repository.

---

**Note:** This is a demonstration project. For production use, implement additional security measures:

- Input validation and sanitization
- Rate limiting
- Authentication and authorization
- Secure token storage
- Error handling improvements
- Transaction logging
- Webhook integration for real-time payment notifications
- HTTPS in production
