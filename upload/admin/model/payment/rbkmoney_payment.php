<?php

class ModelPaymentRBKmoneyPayment extends Model
{
    public function install()
    {
        $query = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "rbkmoney_payment_order` (
		    `rbkmoney_payment_order_id` INT NOT NULL AUTO_INCREMENT , 
		    `order_id` VARCHAR(50) NOT NULL ,
		    `invoice_id` VARCHAR(50) NOT NULL , 
		    `date_added` DATETIME NOT NULL ,
		PRIMARY KEY (`rbkmoney_payment_order_id`))
        ENGINE = MyISAM CHARSET=utf8 COLLATE utf8_general_ci;";

        $this->db->query($query);
    }

    public function uninstall()
    {
        $query = "DROP TABLE IF EXISTS `" . DB_PREFIX . "rbkmoney_payment_order`;";
        $this->db->query($query);
    }
}
