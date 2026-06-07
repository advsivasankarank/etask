USE etaxadv_etask;

ALTER TABLE client_portal_credentials
    ADD COLUMN portal_label VARCHAR(190) NULL AFTER portal_code;
