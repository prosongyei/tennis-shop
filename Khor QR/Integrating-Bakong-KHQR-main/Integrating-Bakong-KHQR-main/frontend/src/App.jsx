import React, { useEffect, useState } from 'react';
import { QRCodeSVG } from 'qrcode.react';
import { generatekhqr_api } from './api/generatekhqr.api';
import { checkpayment_api } from './api/checkpayment.api';

const App = () => {
  const [userId, setUserId] = useState(6);
  const [orderId, setOrderId] = useState(null);
  const [qrData, setQrData] = useState(null);
  const [loading, setLoading] = useState(false);
  const [paymentStatus, setPaymentStatus] = useState('idle'); 
  const [error, setError] = useState(null);
  const [countdown, setCountdown] = useState(null);


  const generateQRCode = async () => {
    setLoading(true);
    setError(null);
    setPaymentStatus('idle');
    
    try {
      const response = await generatekhqr_api(userId);
      
      if (response.success) {
        setOrderId(response.data.id);
        setQrData(response.data);
        setPaymentStatus('pending');
      }
    } catch (error) {
      setError(error.response?.data?.message || error.message);
      console.error('Error generating QR code:', error);
    } finally {
      setLoading(false);
    }
  };


    useEffect(() => {
        if (paymentStatus !== 'pending' || !userId || !qrData?.qr_md5) return;

        console.log('Starting payment polling...');
        
        const interval = setInterval(async () => {
        console.log('Checking payment status...');
        
        try {
            const response = await checkpayment_api(userId, qrData.qr_md5);
            
            if (response.success) {
            console.log('Payment confirmed!');
            setPaymentStatus('paid');
            clearInterval(interval);
            }
        } catch (error) {
            console.log('Payment not confirmed yet');
        }
        }, 2000); 
        return () => {
        console.log('Stopping payment polling');
        clearInterval(interval);
        };
    }, [paymentStatus, userId, qrData]);

  // Step 3: Countdown timer for QR expiration
  useEffect(() => {
    if (!qrData?.qr_expiration || paymentStatus !== 'pending') return;

    const updateCountdown = () => {
      const now = Date.now();
      const expirationTime = new Date(qrData.qr_expiration).getTime();
      const remaining = expirationTime - now;
      
      if (remaining <= 0) {
        setCountdown('00:00');
        setError('QR code has expired. Please generate a new one.');
        setPaymentStatus('idle');
        return;
      }
      
      const minutes = Math.floor(remaining / 60000);
      const seconds = Math.floor((remaining % 60000) / 1000);
      setCountdown(`${minutes}:${seconds.toString().padStart(2, '0')}`);
    };

    updateCountdown();
    const timer = setInterval(updateCountdown, 1000);

    return () => clearInterval(timer);
  }, [qrData, paymentStatus]);

  // Reset everything
  const reset = () => {
    setOrderId(null);
    setQrData(null);
    setPaymentStatus('idle');
    setError(null);
    setCountdown(null);
  };

  return (
    <div className="min-h-screen flex justify-center items-center bg-gray-100 p-5">
      <div className=" rounded-xl p-10 max-w-lg w-full">
        <h1 className="text-3xl font-bold text-center mb-8 text-gray-800">
           Bakong KHQR Payment
        </h1>

        {error && (
          <div className="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-4 text-center">
            ⚠️ {error}
          </div>
        )}

        {/* Initial State - Generate QR */}
        {paymentStatus === 'idle' && (
          <div className="text-center">
            <button 
              onClick={generateQRCode} 
              disabled={loading}
              className="w-full py-4 text-base font-semibold text-white bg-green-500 rounded-lg hover:bg-green-600 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed"
            >
              {loading ? 'Generating...' : ' Generate QR Code'}
            </button>
          </div>
        )}

        {/* Pending State - Show QR and Wait for Payment */}
        {paymentStatus === 'pending' && qrData && (
          <div className="text-center">
            <div className="bg-gray-50 p-4 rounded-lg mb-5">
              <div className="flex justify-between mb-2">
                <span className="text-gray-600 font-medium">Merchant:</span>
                <span className="text-gray-800">{qrData.marchant_name}</span>
              </div>
              <div className="flex justify-between mb-2">
                <span className="text-gray-600 font-medium">Amount:</span>
                <span className="text-gray-800">${qrData.amount} {qrData.currency}</span>
              </div>
              <div className="flex justify-between mb-2">
                <span className="text-gray-600 font-medium">Order ID:</span>
                <span className="text-gray-800">#{qrData.id}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600 font-medium">Expires in:</span>
                <span className="text-orange-500 font-bold">{countdown || 'Loading...'}</span>
              </div>
            </div>

            <div className="flex justify-center mb-5">
              <div className="border-2 border-gray-300 rounded-lg p-4 ">
                <QRCodeSVG 
                  value={qrData.qr_code} 
                  size={280}
                  level="H"
                />
              </div>
            </div>

            <p className="text-sm text-gray-500 mb-4">
              ⏳ Auto-checking payment status...
            </p>

            <button
              onClick={reset}
              className="w-full py-3 text-base font-semibold text-white bg-gray-500 rounded-lg hover:bg-gray-600 transition-colors"
            >
              Cancel
            </button>
          </div>
        )}

        {/* Success State - Payment Completed */}
        {paymentStatus === 'paid' && (
          <div className="text-center py-5">
            <div className="text-6xl text-green-500 mb-4">✓</div>
            <h2 className="text-2xl font-bold text-green-500 mb-2">
              Payment Successful!
            </h2>
            <p className="text-base text-gray-600 mb-5">
               Your transaction has been completed successfully!
            </p>
            <div className="bg-green-50 p-4 rounded-lg mb-5">
              <div className="flex justify-between mb-2">
                <span className="text-gray-600 font-medium">Order ID:</span>
                <span className="text-gray-800">#{orderId}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600 font-medium">Amount Paid:</span>
                <span className="text-gray-800">${qrData.amount} {qrData.currency}</span>
              </div>
            </div>
            <button 
              onClick={reset}
              className="w-full py-4 text-base font-semibold text-white bg-green-500 rounded-lg hover:bg-green-600 transition-colors"
            >
               Make Another Payment
            </button>
          </div>
        )}
      </div>
    </div>
  );
};

export default App;