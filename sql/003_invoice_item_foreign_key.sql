ALTER TABLE t_invoice_item
    ADD FOREIGN KEY fk_item_invoice (invoice_id)
    REFERENCES t_invoice(id);