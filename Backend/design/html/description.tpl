{$meta_title = $btr->sviat__invoice_payment__title|escape scope=global}

<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="wrap_heading">
            <div class="box_heading heading_page">
                {$btr->sviat__invoice_payment__title}
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="alert alert--icon alert--info">
            <div class="alert__content">
                <div class="alert__title">{$btr->alert_info|escape}</div>
                <p>{$btr->sviat__invoice_payment__description}</p>
            </div>
        </div>
    </div>
</div>

{if $message_success == 'saved'}
<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="alert alert--center alert--icon alert--success">
            <div class="alert__content">
                <div class="alert__title">{$btr->general_settings_saved|escape}</div>
            </div>
        </div>
    </div>
</div>
{/if}

<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="session_id" value="{$smarty.session.id}">

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="boxed fn_toggle_wrap">
                <div class="heading_box">
                    <span>{$btr->sviat__invoice_payment__invoice_header_settings|escape}</span>
                </div>
                <div class="toggle_body_wrap on">
                    <div class="row">
                        <div class="col-lg-6 col-md-12">
                            <div class="mb-1">
                                <div class="heading_label">{$btr->sviat__invoice_payment__supplier_type|escape}</div>
                                <input type="text" name="sviat__invoice_payment__supplier_type" class="form-control" value="{$invoice_settings->supplier_type|escape}" placeholder="{$btr->sviat__invoice_payment__supplier_type_placeholder|escape}">
                            </div>
                            <div class="mb-1">
                                <div class="heading_label">{$btr->sviat__invoice_payment__supplier_name|escape}</div>
                                <input type="text" name="sviat__invoice_payment__supplier_name" class="form-control" value="{$invoice_settings->supplier_name|escape}">
                            </div>
                            <div class="mb-1">
                                <div class="heading_label">{$btr->sviat__invoice_payment__supplier_bank_account|escape}</div>
                                <input type="text" name="sviat__invoice_payment__supplier_bank_account" class="form-control" value="{$invoice_settings->supplier_bank_account|escape}" placeholder="UA...">
                            </div>
                            <div class="mb-1">
                                <div class="heading_label">{$btr->sviat__invoice_payment__supplier_bank_name|escape}</div>
                                <input type="text" name="sviat__invoice_payment__supplier_bank_name" class="form-control" value="{$invoice_settings->supplier_bank_name|escape}">
                            </div>
                            <div class="mb-1">
                                <div class="heading_label">{$btr->sviat__invoice_payment__supplier_address|escape}</div>
                                <input type="text" name="sviat__invoice_payment__supplier_address" class="form-control" value="{$invoice_settings->supplier_address|escape}">
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-12">
                            <div class="mb-1">
                                <div class="heading_label">{$btr->sviat__invoice_payment__supplier_recipient_code|escape}</div>
                                <input type="text" name="sviat__invoice_payment__supplier_recipient_code" class="form-control" value="{$invoice_settings->supplier_recipient_code|escape}">
                            </div>
                            <div class="mb-1">
                                <div class="heading_label">{$btr->sviat__invoice_payment__supplier_tax_info|escape}</div>
                                <input type="text" name="sviat__invoice_payment__supplier_tax_info" class="form-control" value="{$invoice_settings->supplier_tax_info|escape}" placeholder="Платник єдиного податку 2 групи, ставка 20 %">
                            </div>
                            <div class="mb-1">
                                <div class="heading_label">{$btr->sviat__invoice_payment__place_of_compilation|escape}</div>
                                <input type="text" name="sviat__invoice_payment__place_of_compilation" class="form-control" value="{$invoice_settings->place_of_compilation|escape}" placeholder="м. Київ">
                            </div>
                            <div class="mb-1">
                                <div class="heading_label">{$btr->sviat__invoice_payment__supplier_signature_name|escape}</div>
                                <input type="text" name="sviat__invoice_payment__supplier_signature_name" class="form-control" value="{$invoice_settings->supplier_signature_name|escape}" placeholder="Шевченко Т.Г.">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <button type="submit" class="btn btn_small btn_blue">
                {include file='svg_icon.tpl' svgId='checked'}
                <span>{$btr->general_apply|escape}</span>
            </button>
        </div>
    </div>
</form>
