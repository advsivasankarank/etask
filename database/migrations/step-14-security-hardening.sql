ALTER TABLE clients
    ADD COLUMN aadhaar_ciphertext TEXT NULL AFTER aadhaar_no,
    ADD COLUMN aadhaar_iv VARCHAR(255) NULL AFTER aadhaar_ciphertext;
