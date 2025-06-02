-- Add Google ID column to users table
ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL AFTER email;

-- Update orders table to support downpayment and payment proof
ALTER TABLE orders ADD COLUMN downpayment_amount DECIMAL(10,2) DEFAULT 0 AFTER total_amount;
ALTER TABLE orders ADD COLUMN payment_proof_file VARCHAR(255) NULL AFTER payment_status;
ALTER TABLE orders ADD COLUMN reference_number VARCHAR(100) NULL AFTER payment_proof_file;
ALTER TABLE orders ADD COLUMN bank_owner_name VARCHAR(255) NULL AFTER reference_number;
ALTER TABLE orders ADD COLUMN bank_name VARCHAR(100) NULL AFTER bank_owner_name;

-- Create uploads directory structure (you'll need to create these folders manually)
-- /isabelle-prints/uploads/payment-proofs/