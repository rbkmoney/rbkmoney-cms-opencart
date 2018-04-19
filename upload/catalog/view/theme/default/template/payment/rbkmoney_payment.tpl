<?php if (!empty($errormsg)) { ?>
<?php echo $errormsg ?>
<?php } else { ?>

<?php if (!empty($form_css_button)) { ?>
<style>
    <?php echo $form_css_button ?>
</style>
<?php } ?>

<form action="<?php echo $payment_form_success_url ?>" method="POST">
    <script src="<?php echo $payment_form_url ?>" class="rbkmoney-checkout"
            data-invoice-id="<?php echo $invoice_id; ?>"
            data-invoice-access-token="<?php echo $invoice_access_token; ?>"
    <?php if (!empty($form_company_name)) { ?>
    data-name="<?php echo $form_company_name ?>"
    <?php } ?>
    <?php if (!empty($form_button_label)) { ?>
    data-label="<?php echo $form_button_label ?>"
    <?php } ?>
    <?php if (!empty($form_description)) { ?>
    data-logo="<?php echo $form_description ?>"
    <?php } ?>
    >
    </script>
    </form>
    <?php } ?>
