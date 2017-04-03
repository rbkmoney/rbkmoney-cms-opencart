<?php if (!empty($errormsg)) { ?>
<?php echo $errormsg ?>
<?php } else { ?>
<script src="<?php echo $action ?>" class="rbkmoney-checkout"
    data-invoice-id="<?php echo $invoice_id; ?>"
    data-invoice-access-token="<?php echo $invoice_access_token; ?>"
    data-endpoint-success="<?php echo $success_redirect_url; ?>"
    data-endpoint-failed="<?php echo $failed_redirect_url; ?>"
    data-amount="<?php echo $amount ?>"
    data-currency="<?php echo $currency ?>"
    <?php if (!empty($form_company_name)) { ?>
    data-name="<?php echo $form_company_name ?>"
    <?php } ?>
    <?php if (!empty($form_path_logo)) { ?>
    data-logo="<?php echo $form_path_logo ?>"
    <?php } ?>
>
</script>
<?php } ?>
