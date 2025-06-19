-- Update schema untuk membuat product_id nullable di order_items
-- Jalankan script ini jika database sudah dibuat sebelumnya

USE uaspbw_db;

ALTER TABLE order_items DROP FOREIGN KEY order_items_ibfk_2;

ALTER TABLE order_items MODIFY COLUMN product_id INT NULL;


ALTER TABLE order_items ADD CONSTRAINT order_items_ibfk_2 
FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT;


DESCRIBE order_items;
