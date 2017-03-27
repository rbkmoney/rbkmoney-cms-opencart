<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" form="form-globalpay" data-toggle="tooltip" title="<?php echo $button_save; ?>"
                        class="btn btn-primary"><i class="fa fa-save"></i></button>
                <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>"
                   class="btn btn-default"><i class="fa fa-reply"></i></a></div>
            <h1><?php echo $heading_title; ?></h1>
            <ul class="breadcrumb">
                <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>

    <div class="container-fluid">
        <?php if ($error_warning) { ?>
        <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        <?php } ?>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i><?php echo $text_edit; ?></h3>
            </div>
            <div class="panel-body">
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data"
                      id="form-rbkmoney-payment" class="form-horizontal">

                    <table width=100%>
                        <tr>
                            <td width=10 valign=top>
                                <a href="https://rbk.money" onclick="return !window.open(this.href)">
                                    <img src="view/image/payment/rbkmoney_payment.png" alt="RBKmoney"
                                         title="RBKmoney" style="border: 1px solid #EEEEEE;"/>
                                </a>
                            </td>
                        </tr>
                    </table>

                    <div class="tab-content">
                        <!-- Shop ID -->
                        <div class="form-group required">
                            <label class="col-sm-2 control-label" for="rbkmoney_payment_shop_id">
                                <?php echo $entry_shop_id; ?>
                                <span data-toggle="tooltip" title="<?php echo $help_shop_id; ?>"></span>
                            </label>
                            <div class="col-sm-10">
                                <input type="text" name="rbkmoney_payment_shop_id"
                                       value="<?php echo $rbkmoney_payment_shop_id; ?>"
                                       placeholder="<?php echo $help_shop_id; ?>" id="input-shop-id"
                                       class="form-control"/>
                                <?php if ($error_shop_id) { ?>
                                <div class="text-danger"><?php echo $error_shop_id; ?></div>
                                <?php } ?>
                            </div>
                        </div>

                        <!-- From path to logo -->
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="rbkmoney_form_path_logo">
                                <span data-toggle="tooltip" title="<?php echo $help_form_path_logo; ?>">
                                    <?php echo $entry_form_path_logo; ?>
                                </span>
                            </label>
                            <div class="col-sm-10">
                                <input type="text" name="rbkmoney_payment_form_path_logo" value="<?php echo $rbkmoney_payment_form_path_logo; ?>"
                                       placeholder="<?php echo $help_form_path_logo; ?>" id="rbkmoney_payment_form_path_logo"
                                       class="form-control"/>
                            </div>
                        </div>

                        <!-- From company name -->
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="rbkmoney_form_company_name">
                                <span data-toggle="tooltip" title="<?php echo $help_form_company_name; ?>">
                                    <?php echo $entry_form_company_name; ?>
                                </span>
                            </label>
                            <div class="col-sm-10">
                                <input type="text" name="rbkmoney_payment_form_company_name" value="<?php echo $rbkmoney_payment_form_company_name; ?>"
                                       placeholder="<?php echo $help_form_company_name; ?>" id="rbkmoney_payment_form_company_name"
                                       class="form-control"/>
                            </div>
                        </div>

                        <!-- Private key -->
                        <div class="form-group required">
                            <label class="col-sm-2 control-label" for="rbkmoney_payment_private_key">
                                <?php echo $entry_private_key; ?>
                                <span data-toggle="tooltip" title="<?php echo $help_private_key; ?>"></span>
                            </label>
                            <div class="col-sm-10">
                                <input type="text" name="rbkmoney_payment_private_key"
                                       value="<?php echo $rbkmoney_payment_private_key; ?>"
                                       placeholder="<?php echo $help_private_key; ?>" id="input-private_key"
                                       class="form-control"/>
                                <?php if ($error_private_key) { ?>
                                <div class="text-danger"><?php echo $error_private_key; ?></div>
                                <?php } ?>
                            </div>
                        </div>

                        <!-- Callback public key -->
                        <div class="form-group required">
                            <label class="col-sm-2 control-label" for="rbkmoney_payment_callback_public_key">
                                <?php echo $entry_callback_public_key; ?>
                                <span data-toggle="tooltip" title="<?php echo $help_callback_public_key; ?>"></span>
                            </label>
                            <div class="col-sm-10">
                                <input type="text" name="rbkmoney_payment_callback_public_key"
                                       value="<?php echo $rbkmoney_payment_callback_public_key; ?>"
                                       placeholder="<?php echo $help_callback_public_key; ?>"
                                       id="input-callback_public_key"
                                       class="form-control"/>
                                <?php if ($error_callback_public_key) { ?>
                                <div class="text-danger"><?php echo $error_callback_public_key; ?></div>
                                <?php } ?>
                            </div>
                        </div>

                        <!-- Currency -->
                        <div class="form-group required">
                            <label class="col-sm-2 control-label" for="rbkmoney_payment_currency">
                                <?php echo $entry_currency; ?>
                                <span data-toggle="tooltip" title="<?php echo $help_currency; ?>"></span>
                            </label>
                            <div class="col-sm-10">
                                <input type="text" name="rbkmoney_payment_currency"
                                       value="<?php echo $rbkmoney_payment_currency; ?>"
                                       placeholder="<?php echo $help_currency; ?>" id="input-currency"
                                       class="form-control"/>
                                <?php if ($error_currency) { ?>
                                <div class="text-danger"><?php echo $error_currency; ?></div>
                                <?php } ?>
                            </div>
                        </div>

                        <!-- Payment sort order -->
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="rbkmoney_payment_sort_order">
                                <span data-toggle="tooltip" title="<?php echo $help_sort_order; ?>">
                                    <?php echo $entry_sort_order; ?>
                                </span>
                            </label>
                            <div class="col-sm-10">
                                <input type="text" name="rbkmoney_payment_sort_order"
                                       value="<?php echo $rbkmoney_payment_sort_order; ?>"
                                       placeholder="<?php echo $help_sort_order; ?>" id="rbkmoney_payment_sort_order"
                                       class="form-control"/>
                            </div>
                        </div>

                        <!-- Enable/Disable -->
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="rbkmoney_payment_status">
                                <span data-toggle="tooltip" title="<?php echo $help_status; ?>">
                                    <?php echo $entry_status; ?>
                                </span>
                            </label>
                            <div class="col-sm-10">
                                <select name="rbkmoney_payment_status" id="rbkmoney_payment_status"
                                        class="form-control">
                                    <?php if ($entry_status) { ?>
                                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                    <option value="0"><?php echo $text_disabled; ?></option>
                                    <?php } else { ?>
                                    <option value="1"><?php echo $text_enabled; ?></option>
                                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <!-- Notify URL -->
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="input-callback">
                                <span data-toggle="tooltip" title="<?php echo htmlspecialchars($help_notify_url); ?>">
                                     <?php echo $entry_notify_url; ?>
                                </span>
                            </label>
                            <div class="col-sm-10">
                                <textarea rows="1" readonly id="input-callback"
                                          class="form-control"><?php echo $notify_url; ?></textarea>
                            </div>
                        </div>

                        <!-- Payment order status -->
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="rbkmoney_payment_order_status_id"><span data-toggle="tooltip" title="<?php echo $help_rbkmoney_payment_order_status_id; ?>"><?php echo $entry_rbkmoney_payment_order_status_id; ?></span></label>
                            <div class="col-sm-10">
                                <select name="rbkmoney_payment_order_status_id" id="rbkmoney_payment_order_status_id" class="form-control">
                                    <?php foreach ($order_statuses as $order_status) { ?>
                                    <?php if ($order_status['order_status_id'] == $rbkmoney_payment_order_status_id) { ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected">
                                        <?php echo $order_status['name']; ?>
                                    </option>
                                    <?php } else { ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>">
                                        <?php echo $order_status['name']; ?>
                                    </option>
                                    <?php } ?>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <!-- Payment order status progress -->
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="rbkmoney_payment_order_status_progress_id">
                                <span data-toggle="tooltip" title="<?php echo $help_rbkmoney_payment_order_status_progress_id; ?>">
                                    <?php echo $entry_rbkmoney_payment_order_status_progress_id; ?>
                                </span>
                            </label>
                            <div class="col-sm-10">
                                <select name="rbkmoney_payment_order_status_progress_id" id="rbkmoney_payment_order_status_progress_id" class="form-control">
                                    <?php foreach ($order_statuses as $order_status) { ?>
                                    <?php if ($order_status['order_status_id'] == $rbkmoney_payment_order_status_progress_id) { ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected">
                                        <?php echo $order_status['name']; ?>
                                    </option>
                                    <?php } else { ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>">
                                        <?php echo $order_status['name']; ?>
                                    </option>
                                    <?php } ?>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                    </div>
                </form>
                <div style="text-align:center; color:#555555;">RBKmoney v<?php echo $rbkmoney_payment_version; ?></div>
            </div>
        </div>
    </div>
</div>
<?php echo $footer; ?>
